<?php

namespace Modules\Projects\App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Projects\App\Http\Models\Project;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['super-admin', 'superadmin'])) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        if ($project->created_by === $user->id || $project->isManager($user->id)) {
            return true;
        }

        return $project->userHasPermission($user->id, 'projects.view');
    }

    public function create(User $user): bool
    {
        return $user->can('projects.create') || $user->can('projects.manage');
    }

    public function update(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'projects.edit');
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->isManager($user->id) && $user->can('projects.delete');
    }

    public function changeStatus(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'projects.status');
    }

    public function manageTemplates(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'templates.manage') || $project->isManager($user->id);
    }

    public function applyTemplates(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'templates.apply') || $project->isManager($user->id);
    }

    public function cancel(User $user, Project $project): bool
    {
        if ($project->isManager($user->id)) {
            return true;
        }

        return $project->userHasPermission($user->id, 'projects.cancel') || $user->can('projects.cancel');
    }

    public function managePhases(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'phases.manage');
    }

    public function deletePhases(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'phases.delete');
    }

    public function manageTasks(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'tasks.create') || $project->userHasPermission($user->id, 'tasks.edit');
    }

    public function createTasks(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'tasks.create');
    }

    public function editTasks(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'tasks.edit');
    }

    public function deleteTasks(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'tasks.delete');
    }

    public function assignTasks(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'tasks.assign');
    }

    public function changeTaskStatus(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'tasks.change_status');
    }

    public function cancelTasks(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'tasks.cancel') || $project->userHasPermission($user->id, 'tasks.edit') || $project->isManager($user->id);
    }

    public function viewMessages(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'messages.view');
    }

    public function sendMessage(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'messages.send');
    }

    public function pinMessage(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'messages.pin');
    }

    public function deleteMessage(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'messages.delete');
    }

    public function viewComments(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'comments.view');
    }

    public function sendComment(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'comments.send');
    }

    public function deleteComment(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'comments.delete');
    }

    public function manageDocuments(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'documents.upload') || $project->userHasPermission($user->id, 'documents.delete');
    }

    public function viewDocuments(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'documents.view');
    }

    public function uploadDocument(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'documents.upload');
    }

    public function deleteDocument(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'documents.delete');
    }

    public function viewActivities(User $user, Project $project): bool
    {
        return $project->userHasPermission($user->id, 'activities.view');
    }
}

