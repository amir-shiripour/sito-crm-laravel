<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Projects\App\Http\Models\Project;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectsTaskSseController extends Controller
{
    /**
     * Broadcast an event to project tasks SSE stream listeners.
     */
    public static function broadcastEvent(int $projectId, string $type, array $data): void
    {
        try {
            $cacheKey = 'project_tasks_events_' . $projectId;
            $events = cache()->get($cacheKey, []);

            $eventId = (int)round(microtime(true) * 1000);
            $events[] = [
                'id' => $eventId,
                'type' => $type,
                'data' => array_merge($data, ['event_id' => $eventId]),
                'time' => time(),
            ];

            // Keep rolling buffer of last 50 events
            if (count($events) > 50) {
                $events = array_slice($events, -50);
            }

            cache()->put($cacheKey, $events, now()->addMinutes(10));
        } catch (\Throwable $e) {
            \Log::error('ProjectsTaskSse broadcast error: ' . $e->getMessage());
        }
    }

    /**
     * Stream real-time task and checklist events to client.
     */
    public function stream(Request $request, Project $project): StreamedResponse
    {
        $this->authorize('view', $project);
        $lastEventId = (int)$request->query('last_event_id', (int)round(microtime(true) * 1000));
        $currentUserId = auth()->id();

        return response()->stream(function () use ($project, $lastEventId, $currentUserId) {
            if (ob_get_level() > 0) {
                ob_end_flush();
            }

            $timeout = 0;
            $maxTimeout = 55; // 55s per stream connection before client reconnects

            while ($timeout < $maxTimeout) {
                echo ': heartbeat' . PHP_EOL . PHP_EOL;
                if (ob_get_level() > 0) ob_flush();
                flush();

                $cacheKey = 'project_tasks_events_' . $project->id;
                $events = cache()->get($cacheKey, []);

                if (!empty($events)) {
                    foreach ($events as $event) {
                        if ($event['id'] > $lastEventId) {
                            $eventData = $event['data'];
                            $eventData['is_my_action'] = isset($eventData['triggered_by_user_id']) && (int)$eventData['triggered_by_user_id'] === (int)$currentUserId;

                            echo 'id: ' . $event['id'] . PHP_EOL;
                            echo 'event: ' . $event['type'] . PHP_EOL;
                            echo 'data: ' . json_encode($eventData, JSON_UNESCAPED_UNICODE) . PHP_EOL;
                            echo PHP_EOL;

                            if (ob_get_level() > 0) ob_flush();
                            flush();

                            $lastEventId = $event['id'];
                        }
                    }
                }

                sleep(1); // 1 second real-time resolution
                $timeout += 1;

                if (connection_aborted()) {
                    break;
                }
            }

            // Instruct client to reconnect cleanly
            echo 'event: reconnect' . PHP_EOL;
            echo 'data: ' . json_encode(['last_event_id' => $lastEventId]) . PHP_EOL;
            echo PHP_EOL;
            flush();

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}
