pp<?php
namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine if the given project can be viewed by the user.
     */
    public function view(User $user, Project $project)
    {
        return $project->user_id === $user->id;
    }

    /**
     * Determine if the given project can be updated by the user.
     */
    public function update(User $user, Project $project)
    {
        return $project->user_id === $user->id;
    }
}

