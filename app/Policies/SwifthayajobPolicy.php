<?php

namespace App\Policies;

use App\Models\Swifthayajob;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SwifthayajobPolicy
{
  public function view(User $user, Swifthayajob $job): bool
  {
    return ($user->user_type === "admin" || $user->id === $job->employer_id);
  }


  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, Swifthayajob $job): bool
  {
    return ($user->user_type === "admin" || $user->id === $job->employer_id);
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, Swifthayajob $job): bool
  {
    return ($user->user_type === "admin" || $user->id === $job->employer_id);
  }

}
