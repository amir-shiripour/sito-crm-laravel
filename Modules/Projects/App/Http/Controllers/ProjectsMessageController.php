<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectMessage;
use Illuminate\Support\Str;

class ProjectsMessageController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $messages = $project->messages()->with('user')->oldest()->get();

        return response()->json($messages);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('sendMessage', $project);

        $request->validate([
            'body' => 'required|string|max:5000',
            'attachments' => 'nullable|array',
            'parent_id' => 'nullable|integer|exists:projects_messages,id',
        ]);

        $parentId = $request->parent_id;
        if ($parentId) {
            $parentExists = $project->messages()->where('id', $parentId)->exists();
            if (!$parentExists) {
                $parentId = null;
            }
        }

        $message = $project->messages()->create([
            'parent_id' => $parentId,
            'user_id' => auth()->id(),
            'body' => $request->body,
            'attachments' => $request->attachments,
        ]);

        ProjectActivity::log(
            projectId: $project->id,
            action: 'message.sent',
            subject: 'ارسال پیام: ' . Str::limit($message->body, 60),
            userId: auth()->id()
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($message->load(['user', 'parent.user']), 201);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'messages'])
            ->with('success', 'پیام شما با موفقیت ارسال شد.');
    }

    public function destroy(Project $project, ProjectMessage $message)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasAnyRole(['super-admin', 'superadmin']);
        $isOwner = $message->user_id === $user?->id;
        $canDeleteOthers = $project->userHasPermission($user?->id, 'messages.delete');

        if (!$isOwner && !$isSuperAdmin && !$canDeleteOthers) {
            abort(403, 'شما فقط مجاز به حذف پیام‌های خودتان هستید.');
        }

        $messageId = $message->id;
        $message->delete();
        $deletedKey = 'project_deleted_messages_' . $project->id;
        $existing = cache()->get($deletedKey, []);
        $existing[] = $messageId;
        cache()->put($deletedKey, $existing, 300); // 5 minutes

        ProjectActivity::log(
            projectId: $project->id,
            action: 'message.deleted',
            subject: 'حذف پیام توسط: ' . auth()->user()->name,
            userId: auth()->id()
        );

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'messages'])
            ->with('success', 'پیام با موفقیت حذف شد.');
    }

    public function togglePin(Project $project, ProjectMessage $message)
    {
        $this->authorize('pinMessage', $project);

        $isCurrentlyPinned = (bool) $message->is_pinned;

        $message->update([
            'is_pinned' => !$isCurrentlyPinned,
            'pinned_at' => !$isCurrentlyPinned ? now() : null,
            'pinned_by' => !$isCurrentlyPinned ? auth()->id() : null,
        ]);

        // Notify SSE clients about pin update
        $pinKey = 'project_pin_updates_' . $project->id;
        $existing = cache()->get($pinKey, []);
        $existing[] = ['id' => $message->id, 'is_pinned' => !$isCurrentlyPinned];
        cache()->put($pinKey, $existing, 300);

        $pinAction = !$isCurrentlyPinned ? 'message.pinned' : 'message.unpinned';
        ProjectActivity::log(
            projectId: $project->id,
            action: $pinAction,
            subject: (!$isCurrentlyPinned ? 'پین شد: ' : 'آنپین شد: ') . Str::limit($message->body, 50),
            userId: auth()->id()
        );

        $statusMsg = !$isCurrentlyPinned ? 'پیام با موفقیت سنجاق (پین) شد.' : 'پیام از حالت سنجاق خارج شد.';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'ok' => true,
                'is_pinned' => (bool) $message->is_pinned,
                'message' => $statusMsg,
            ]);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'messages'])
            ->with('success', $statusMsg);
    }
}
