<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectsMessageSseController extends Controller
{
    /**
     * Handle real-time project messages with lightweight non-blocking polling.
     */
    public function poll(Request $request, Project $project): JsonResponse|StreamedResponse
    {
        $this->authorize('view', $project);
        $lastId = (int)$request->query('last_id', 0);
        $since = (int)$request->query('since', 0);
        $currentUserId = auth()->id();

        // 1. Check for new messages
        $newMessages = [];
        $cachedMaxId = cache()->get('project_last_message_id_' . $project->id);

        if ($cachedMaxId === null || $lastId < $cachedMaxId) {
            $messages = ProjectMessage::where('project_id', $project->id)
                ->where('id', '>', $lastId)
                ->with(['user', 'parent.user'])
                ->orderBy('id')
                ->get();

            if ($messages->isNotEmpty()) {
                cache()->put('project_last_message_id_' . $project->id, $messages->last()->id, 3600);
                $newMessages = $messages->map(function ($message) use ($currentUserId) {
                    return [
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
                })->values()->all();
            }
        }

        // 2. Check for deleted messages
        $deletedKey = 'project_deleted_messages_' . $project->id;
        $deletedEntries = cache()->get($deletedKey, []);
        $deletedIds = [];

        if (!empty($deletedEntries)) {
            foreach ($deletedEntries as $entry) {
                if (is_array($entry)) {
                    if ($since <= 0 || ($entry['time'] ?? 0) >= $since) {
                        $deletedIds[] = $entry['id'];
                    }
                } else {
                    $deletedIds[] = (int)$entry;
                }
            }
            $deletedIds = array_values(array_unique($deletedIds));
        }

        // 3. Check for pin updates
        $pinKey = 'project_pin_updates_' . $project->id;
        $pinEntries = cache()->get($pinKey, []);
        $pinUpdates = [];

        if (!empty($pinEntries)) {
            foreach ($pinEntries as $entry) {
                if (is_array($entry)) {
                    if ($since <= 0 || ($entry['time'] ?? 0) >= $since) {
                        $pinUpdates[] = [
                            'id' => $entry['id'],
                            'is_pinned' => (bool)$entry['is_pinned'],
                        ];
                    }
                }
            }
        }

        // Single-shot SSE compatibility if client connects via EventSource
        if (str_contains($request->header('Accept', ''), 'text/event-stream')) {
            return response()->stream(function () use ($newMessages, $deletedIds, $pinUpdates) {
                if (ob_get_level() > 0) ob_end_flush();

                echo 'retry: 3000' . PHP_EOL . PHP_EOL;

                foreach ($newMessages as $payload) {
                    echo 'event: new_message' . PHP_EOL;
                    echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
                }

                if (!empty($deletedIds)) {
                    echo 'event: messages_deleted' . PHP_EOL;
                    echo 'data: ' . json_encode(['ids' => $deletedIds], JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
                }

                if (!empty($pinUpdates)) {
                    echo 'event: pin_updated' . PHP_EOL;
                    echo 'data: ' . json_encode(['updates' => $pinUpdates], JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
                }

                echo 'event: reconnect' . PHP_EOL;
                echo 'data: {}' . PHP_EOL . PHP_EOL;
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
            'messages' => $newMessages,
            'deleted_ids' => $deletedIds,
            'pin_updates' => $pinUpdates,
            'timestamp' => time(),
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
