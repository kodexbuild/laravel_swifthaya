<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Gate::define("admin", function (User $user) {
      return $user->user_type === "admin" && $user->user_status !== "banned";
    });
    Gate::define("talent", function (User $user) {
      return $user->user_type === "admin" || $user->user_type === "talent" && $user->user_status !== "banned";
    });
    Gate::define("company", function (User $user) {
      return $user->user_type === "admin" || $user->user_type === "company" && $user->user_status !== "banned";
    });
    Gate::define("individual", function (User $user) {
      return $user->user_type === "admin" || $user->user_type === "individual" && $user->user_status !== "banned";
    });

    Gate::define("employer", function (User $user) {
      return $user->user_type === "admin" || $user->user_type === "individual" || $user->user_type === "company" && $user->user_status !== "banned";
    });
  }
}
