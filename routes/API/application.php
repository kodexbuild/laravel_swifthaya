<?php

use App\Http\Controllers\API\V1\ApplicationController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {

  // Fetch applicants for a specific job
  Route::get('jobs/{job}/applicants', [ApplicationController::class, 'viewJobApplicants'])
    ->middleware(['can:employer']);

  Route::middleware(['can:employer'])->prefix('/applications')->group(function () {

    // Accept application
    Route::patch('/{application}/accept', [ApplicationController::class, 'accept']);

    // Shortlist application
    Route::patch('/{application}/shortlist', [ApplicationController::class, 'shortlist']);

    // Reject application
    Route::patch('/{application}/reject', [ApplicationController::class, 'reject']);
  });


  // talent
  Route::middleware(['can:talent'])->group(function () {
    Route::prefix('/applications')->group(function () {
      // View all job applications made by the talent
      Route::get('/jobs', [ApplicationController::class, 'jobApplications']);
    });

    // Apply for a job
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'applyJob']);
  });
});
