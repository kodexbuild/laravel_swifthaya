<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApplicationPolicy
{

  public function modify(User $user, Application $application): bool
  {
    if ($application->swifthayajob) {
      return $user->user_type === "admin" || $user->id === $application->swifthayajob->user->id;
    }
    return $user->user_type === "admin" || $user->id === $application->project->user->id;
  }
}
