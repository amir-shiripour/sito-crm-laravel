<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectTask;
use Modules\Projects\App\Http\Models\ProjectTaskComment;

class ProjectsTaskCommentController extends Controller
{
    public function store(Request $request, Project $project, ProjectTask $task)
    {
        $this->authorize('sendComment', $project);

        if ($project->isCanceled() || $task->isCanceled()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'این گروه یا پروژه لغو شده است و امکان ثبت نظر جدید وجود ندارد.',
                ], 422);
            }
            return back()->with('error', 'این گروه یا پروژه لغو شده است و امکان ثبت نظر جدید وجود ندارد.');
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        ProjectActivity::log(
            projectId: $project->id,
            action: 'task.updated',
            subject: "ثبت یادداشت جدید روی کار «{$task->title}»",
            taskId: $task->id,
            userId: auth()->id()
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->load('user'),
                'comments_count' => $task->comments()->count(),
            ], 201);
        }

        return back()->with('success', 'توضیحات با موفقیت ثبت شد.');
    }

    public function destroy(Project $project, ProjectTask $task, ProjectTaskComment $comment)
    {
        if ($project->isCanceled() || $task->isCanceled()) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'این گروه یا پروژه لغو شده است و امکان حذف نظر وجود ندارد.',
                ], 422);
            }
            return back()->with('error', 'این گروه یا پروژه لغو شده است و امکان حذف نظر وجود ندارد.');
        }

        $isSuperAdmin = auth()->user()?->hasAnyRole(['super-admin', 'superadmin']);
        $isOwner = $comment->user_id === auth()->id();
        $isManager = $project->isManager(auth()->id());
        $canDeleteOthers = $project->userHasPermission(auth()->id(), 'comments.delete');

        if (!$isOwner && !$isSuperAdmin && !$isManager && !$canDeleteOthers) {
            abort(403, 'شما مجاز به حذف این نظر نیستید.');
        }

        $comment->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'comments_count' => $task->comments()->count(),
            ]);
        }

        return back()->with('success', 'توضیحات حذف شد.');
    }
}
