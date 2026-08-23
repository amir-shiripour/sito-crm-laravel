<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectsMessageSseController extends Controller
{
    public function stream(Request $request, Project $project): StreamedResponse
    {
        $this->authorize('view', $project);
        $lastId = (int)$request->query('last_id', 0);
        $currentUserId = auth()->id();

        return response()->stream(function () use ($project, $lastId, $currentUserId) {
            if (ob_get_level() > 0) {
                ob_end_flush();
            }

            $timeout = 0;
            $maxTimeout = 55;

            while ($timeout < $maxTimeout) {
                echo ': heartbeat' . PHP_EOL . PHP_EOL;

                if (ob_get_level() > 0) ob_flush();
                flush();
                $newMessages = ProjectMessage::where('project_id', $project->id)
                    ->where('id', '>', $lastId)
                    ->with(['user', 'parent.user'])
                    ->orderBy('id')
                    ->get();

                if ($newMessages->isNotEmpty()) {
                    foreach ($newMessages as $message) {
                        $payload = [
                            'id' => $message->id,
                            'user_id' => $message->user_id,
                            'user_name' => $message->user?->name ?? 'کاربر',
                            'user_initial' => mb_substr($message->user?->name ?? 'U', 0, 1),
                            'body' => $message->body,
                            'is_pinned' => (bool)$message->is_pinned,
                            'parent_id' => $message->parent_id,
                            'parent_body' => $message->parent ? mb_substr($message->parent->body, 0, 60) : null,
                            'parent_user' => $message->parent?->user?->name,
                            'is_mine' => $message->user_id === $currentUserId,
                            'created_at' => $message->created_at?->toISOString(),
                        ];

                        echo 'event: new_message' . PHP_EOL;
                        echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL;
                        echo PHP_EOL;

                        if (ob_get_level() > 0) ob_flush();
                        flush();

                        $lastId = $message->id;
                    }
                }

                // Check for deleted messages (we store deleted message IDs in cache for 5 mins)
                $deletedKey = 'project_deleted_messages_' . $project->id;
                $deletedIds = cache()->get($deletedKey, []);
                if (!empty($deletedIds)) {
                    echo 'event: messages_deleted' . PHP_EOL;
                    echo 'data: ' . json_encode(['ids' => $deletedIds], JSON_UNESCAPED_UNICODE) . PHP_EOL;
                    echo PHP_EOL;

                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    // Clear after sending
                    cache()->forget($deletedKey);
                }

                // Check for pin updates
                $pinKey = 'project_pin_updates_' . $project->id;
                $pinUpdates = cache()->get($pinKey, []);
                if (!empty($pinUpdates)) {
                    echo 'event: pin_updated' . PHP_EOL;
                    echo 'data: ' . json_encode(['updates' => $pinUpdates], JSON_UNESCAPED_UNICODE) . PHP_EOL;
                    echo PHP_EOL;

                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    cache()->forget($pinKey);
                }

                sleep(2); // Poll every 2 seconds
                $timeout += 2;

                if (connection_aborted()) {
                    break;
                }
            }

            // Tell client to reconnect
            echo 'event: reconnect' . PHP_EOL;
            echo 'data: {}' . PHP_EOL;
            echo PHP_EOL;
            flush();

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',  // Nginx: disable buffering
            'Connection' => 'keep-alive',
        ]);
    }
}
