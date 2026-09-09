<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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
     * Handle real-time task and checklist events with lightweight non-blocking polling.
     */
    public function poll(Request $request, Project $project): JsonResponse|StreamedResponse
    {
        $this->authorize('view', $project);
        $lastEventId = (int)$request->query('last_event_id', 0);
        $currentUserId = auth()->id();

        $cacheKey = 'project_tasks_events_' . $project->id;
        $events = cache()->get($cacheKey, []);

        $newEvents = [];
        $latestId = $lastEventId;

        if (!empty($events)) {
            foreach ($events as $event) {
                if ($event['id'] > $lastEventId) {
                    $eventData = $event['data'];
                    $eventData['is_my_action'] = isset($eventData['triggered_by_user_id']) && (int)$eventData['triggered_by_user_id'] === (int)$currentUserId;

                    $newEvents[] = [
                        'id' => $event['id'],
                        'type' => $event['type'],
                        'data' => $eventData,
                    ];

                    if ($event['id'] > $latestId) {
                        $latestId = $event['id'];
                    }
                }
            }
        }

        // Single-shot SSE compatibility if client connects via EventSource
        if (str_contains($request->header('Accept', ''), 'text/event-stream')) {
            return response()->stream(function () use ($newEvents, $latestId) {
                if (ob_get_level() > 0) ob_end_flush();

                echo 'retry: 3000' . PHP_EOL . PHP_EOL;

                foreach ($newEvents as $event) {
                    echo 'id: ' . $event['id'] . PHP_EOL;
                    echo 'event: ' . $event['type'] . PHP_EOL;
                    echo 'data: ' . json_encode($event['data'], JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
                }

                echo 'event: reconnect' . PHP_EOL;
                echo 'data: ' . json_encode(['last_event_id' => $latestId]) . PHP_EOL . PHP_EOL;

                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'X-Accel-Buffering' => 'no',
                'Connection' => 'close',
            ]);
        }

        // Standard fast JSON response (non-blocking, ~5ms execution)
        return response()->json([
            'ok' => true,
            'events' => $newEvents,
            'last_event_id' => $latestId,
            'server_time' => (int)round(microtime(true) * 1000),
        ]);
    }

    /**
     * Alias for backward compatibility with route definitions.
     */
    public function stream(Request $request, Project $project): JsonResponse|StreamedResponse
    {
        return $this->poll($request, $project);
    }
}
