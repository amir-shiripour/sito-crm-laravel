<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectChecklistComment;
use Modules\Projects\App\Http\Models\ProjectChecklistItem;
use Modules\Projects\App\Http\Models\ProjectTask;

class ProjectsChecklistCommentController extends Controller
{
    public function store(Request $request, Project $project, ProjectTask $task, ProjectChecklistItem $item)
    {
        $this->authorize('sendComment', $project);

        if ($project->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این پروژه لغو شده است و امکان ثبت کامنت جدید وجود ندارد.',
            ], 422);
        }

        if ($task->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این گروه لغو شده است و امکان ثبت کامنت جدید وجود ندارد.',
            ], 422);
        }

        if ($item->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این کار لغو شده است و امکان ثبت کامنت جدید وجود ندارد.',
            ], 422);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $comment = $item->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        ProjectActivity::log(
            projectId: $project->id,
            action: 'task.updated',
            subject: "کامنت جدید روی کار «{$item->title}» در گروه «{$task->title}»",
            taskId: $task->id,
            userId: auth()->id()
        );

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user'),
            'comments_count' => $item->comments()->count(),
        ], 201);
    }

    public function destroy(Project $project, ProjectTask $task, ProjectChecklistItem $item, ProjectChecklistComment $comment)
    {
        if ($project->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این پروژه لغو شده است و امکان حذف کامنت وجود ندارد.',
            ], 422);
        }

        if ($task->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این گروه لغو شده است و امکان حذف کامنت وجود ندارد.',
            ], 422);
        }

        if ($item->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این کار لغو شده است و امکان حذف کامنت‌های آن وجود ندارد.',
            ], 422);
        }

        $isSuperAdmin = auth()->user()?->hasAnyRole(['super-admin', 'superadmin']);
        $isOwner = $comment->user_id === auth()->id();
        $isManager = $project->isManager(auth()->id());
        $canDeleteOthers = $project->userHasPermission(auth()->id(), 'comments.delete');

        if (!$isOwner && !$isSuperAdmin && !$isManager && !$canDeleteOthers) {
            abort(403, 'شما مجاز به حذف این کامنت نیستید.');
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'comments_count' => $item->comments()->count(),
        ]);
    }
}
