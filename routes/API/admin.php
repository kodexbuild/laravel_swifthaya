<?php

use App\Http\Controllers\API\V1\Admin\ApplicationController;
use App\Http\Controllers\API\V1\Admin\CompanyProfileController;
use App\Http\Controllers\API\V1\Admin\MessageController;
use App\Http\Controllers\API\V1\Admin\PaymentController;
use App\Http\Controllers\API\V1\Admin\ProjectController;
use App\Http\Controllers\API\V1\Admin\ReviewController;
use App\Http\Controllers\API\V1\Admin\SwifthayajobController;
use App\Http\Controllers\API\V1\Admin\TalentProfileController;
use App\Http\Controllers\API\V1\Admin\UserController;
use App\Http\Controllers\API\V1\PaymentController as V1PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', 'can:admin'])->group(function () {

  // User Management Routes
  Route::prefix('users')->group(function () {
    // List all users
    Route::get('/', [UserController::class, 'index']);

    // Get the number of users
    Route::get('/count', [UserController::class, 'count']);

    // show a single user
    Route::post('/{user}', [UserController::class, 'show']);

    // Approve user registration 
    Route::patch('/{user}/approve', [UserController::class, 'approve']);

    // Reject user registration 
    Route::patch('/{user}/reject', [UserController::class, 'reject']);
    //Bann a user
    Route::patch('/{user}/ban', [UserController::class, 'banUser']);

    // Unbann a user
    Route::patch('/{user}/unban', [UserController::class, 'unbanUser']);
  });

  // Review Management Routes
  Route::prefix('reviews')->group(function () {
    // List all reviews
    Route::get('/', [ReviewController::class, 'index']);

    // Get total number of reviews
    Route::get('/count', [ReviewController::class, 'count']);

    // Create a new review
    Route::post('/', [ReviewController::class, 'store']);

    // View a specific review
    Route::get('/{review}', [ReviewController::class, 'show']);

    // Update review information 
    Route::patch('/{review}', [ReviewController::class, 'update']);

    // Delete a review
    Route::delete('/{review}', [ReviewController::class, 'destroy']);

    // Approve a review (Use PATCH for status update)
    Route::patch('/{review}/approve', [ReviewController::class, 'approve']);

    // Reject a review (Use PATCH for status update)
    Route::patch('/{review}/reject', [ReviewController::class, 'reject']);
  });

  // Job Management Routes
  Route::prefix('jobs')->group(function () {
    // List all jobs
    Route::get('/', [SwifthayajobController::class, 'index']);

    // Get the total number of jobs
    Route::get('/count', [SwifthayajobController::class, 'count']);

    // Create a new job
    Route::post('/', [SwifthayajobController::class, 'store']);

    // View a specific job
    Route::get('/{job}', [SwifthayajobController::class, 'show']);

    // Update a job 
    Route::patch('/{job}', [SwifthayajobController::class, 'update']);

    // Delete a job
    Route::delete('/{job}', [SwifthayajobController::class, 'destroy']);

    // Approve a job listing 
    Route::patch('/{job}/approve', [SwifthayajobController::class, 'approve']);

    // Reject a job listing 
    Route::patch('/{job}/reject', [SwifthayajobController::class, 'reject']);
  });

  // // Project Management Routes
  // Route::prefix('projects')->group(function () {
  //   // List all projects
  //   Route::get('/', [ProjectController::class, 'index']);

  //   // Get the total number of projects

  //   Route::get('/count', [ProjectController::class, 'count']);

  //   // Create a new project
  //   Route::post('/', [ProjectController::class, 'store']);

  //   // View a specific project
  //   Route::get('/{project}', [ProjectController::class, 'show']);

  //   // Update project information 
  //   Route::patch('/{project}', [ProjectController::class, 'update']);

  //   // Delete a project
  //   Route::delete('/{project}', [ProjectController::class, 'destroy']);

  //   // Approve a project 
  //   Route::patch('/{project}/approve', [ProjectController::class, 'approve']);

  //   // Reject a project 
  //   Route::patch('/{project}/reject', [ProjectController::class, 'reject']);
  // });

  // Talent Profile Management Routes
  Route::prefix('talents')->group(function () {
    // List all talent profiles
    Route::get('/', [TalentProfileController::class, 'index']);

    Route::get('/count', [TalentProfileController::class, 'count']);

    // View a specific talent profile
    Route::get('/{talent_profile}', [TalentProfileController::class, 'show']);

    // Delete a talent profile
    // Route::delete('/{talent}', [TalentProfileController::class, 'delete']);

    // Approve a talent profile 
    Route::patch('/{talent_profile}/approve', [TalentProfileController::class, 'approve']);

    // Reject a talent profile 
    Route::patch('/{talent_profile}/reject', [TalentProfileController::class, 'reject']);
  });

  // Company Profile Management Routes
  Route::prefix('companies')->group(function () {
    // List all company profiles
    Route::get('/', [CompanyProfileController::class, 'index']);

    // Get the total number of company profiles
    Route::get('/count', [CompanyProfileController::class, 'count']);


    // View a specific company profile
    Route::get('/{company_profile}', [CompanyProfileController::class, 'show']);


    // Delete a company profile
    // Route::delete('/{company_profile}', [CompanyProfileController::class, 'delete']);

    // Approve a company profile 
    Route::patch('/{company_profile}/approve', [CompanyProfileController::class, 'approve']);

    // Reject a company profile 
    Route::patch('/{company_profile}/reject', [CompanyProfileController::class, 'reject']);
  });

  // Application Management Routes
  Route::prefix('applications')->group(function () {
    // Get the total number of job applications
    Route::get('/jobs/count', [ApplicationController::class, 'job_count']);

    // List job applications made by talents
    Route::get('/jobs', [ApplicationController::class, 'index']);

    // Delete an application
    Route::delete('/{application}', [ApplicationController::class, 'destroy']);
  });

  // Message and Conversations Management Routes
  Route::prefix("conversations")->group(function () {

    // Get all conversations
    Route::get('/', [MessageController::class, 'conversations']);
    // Get total number of conversation
    Route::get('/count', [MessageController::class, 'conversation_count']);
    // Delete a conversation
    Route::delete('/{conversation}', [MessageController::class, 'destroyConversation']);
  });
  Route::prefix("messages")->group(function () {

    // Get total number of messages
    Route::get('/count', [MessageController::class, 'message_count']);
    // create a message
    Route::post('/{recipient}', [MessageController::class, 'store_conversation_and_message']);
    // Delete a message
    Route::delete('/{message}', [MessageController::class, 'destroyMessage']);
  });

  // Payment Management Routes
  Route::prefix("payments")->group(function () {

    // make refunds
    Route::post('/refund', [V1PaymentController::class, 'refundPayment'])->middleware("auth");

    Route::get('/', [PaymentController::class, 'index']);
    // total number of payments
    Route::get('/count', [PaymentController::class, 'paymentCount']);

    // total number of refunds
    Route::get('/refunds/count', [PaymentController::class, 'refundCount']);

    Route::get('/{payment}', [PaymentController::class, 'show']);
    Route::delete('/{payment}', [PaymentController::class, 'destroy']);
  });
});
