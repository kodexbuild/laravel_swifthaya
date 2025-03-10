<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User_profile;
use Illuminate\Auth\Access\Response;

class User_profilePolicy
{

  /**
   * Determine whether the user can modify the model.
   */
  public function modify(User $user, User_profile $user_profile): bool
  {
    return ($user->user_type === "admin" || $user->userprofile->id === $user_profile->id);
  }

 
}
