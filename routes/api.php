<?php

use App\Http\Controllers\API\V1\Admin\SwifthayajobController;
use App\Http\Controllers\API\V1\Admin\TalentProfileController;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\MessageController;
use App\Http\Controllers\API\V1\ReviewController;
use App\Http\Controllers\API\V1\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Authentication and Registeration routes
Route::middleware(['guest'])->group(function () {
  Route::post('/login', [AuthController::class, "login"]);
  Route::post('/register_talent', [AuthController::class, "register_talent"]);
  Route::post('/register_company', [AuthController::class, "register_company"]);
  Route::post('/register_individual', [AuthController::class, "register_individual"]);
});

Route::middleware(['auth:sanctum'])->group(function () {

  Route::post('/logout', [AuthController::class, "logout"]);

  // User and Userprofile
  Route::get('users/{user}', [UserController::class, "show"]);
  // update
  Route::patch('users{user}', [UserController::class, "update"]);

  // update / change password
  Route::put('users/password', [UserController::class, "updatePassword"]);

  // update / change profile pic
  Route::post('users/profile_picture', [UserController::class, "profile_img"]);

  // create or update account details
  Route::post('users/account', [UserController::class, "saveAccountDetails"]);
  // delete
  Route::delete('users', [UserController::class, "destroy"]);
});




// talents
require base_path('routes/API/talent.php');
// company
require base_path('routes/API/company.php');
// job
require base_path('routes/API/job.php');
// project
require base_path('routes/API/project.php');
// review
require base_path('routes/API/review.php');
// application
require base_path('routes/API/application.php');
// message
require base_path('routes/API/message.php');

// payment
require base_path('routes/API/payment.php');
// admin
require base_path('routes/API/admin.php');
