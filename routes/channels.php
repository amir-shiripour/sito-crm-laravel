<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('treatment-plan.{id}', function ($user, $id) {
    // Clinic staff check: permit any authenticated staff/user
    return auth()->check();
});

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    if ($user->hasAnyRole(['super-admin', 'superadmin'])) {
        return true;
    }

    $project = \Modules\Projects\App\Http\Models\Project::find($projectId);
    if (!$project) {
        return false;
    }

    if ($project->created_by === $user->id || $project->isManager($user->id)) {
        return true;
    }

    return $project->userHasPermission($user->id, 'projects.view');
});
