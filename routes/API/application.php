<?php

use App\Http\Controllers\API\V1\ApplicationController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {

  // Fetch applicants for a specific job
  Route::get('jobs/{job}/applicants', [ApplicationController::class, 'viewJobApplicants'])
    ->middleware(['can:company']);

  // Fetch applicants for a specific project
  Route::get('projects/{project}/applicants', [ApplicationController::class, 'viewProjectApplicants'])->middleware(['can:individual_company']);


  Route::middleware(['can:individual_company'])->prefix('/applications')->group(function () {

    // Accept application
    Route::patch('/{application}/accept', [ApplicationController::class, 'accept']);

    // Shortlist application
    Route::patch('/{application}/shortlist', [ApplicationController::class, 'shortlist']);

    // Reject application
    Route::patch('/{application}/reject', [ApplicationController::class, 'reject']);
  });


  // talent
  Route::middleware(['can:talent'])->prefix('/applications')->group(function () {

    // View all job applications made by the talent
    Route::get('/jobs', [ApplicationController::class, 'jobApplications']);

    // View all project applications made by the talent
    Route::get('/projects', [ApplicationController::class, 'projectApplications']);

  });
  
  // Apply for a job
  Route::post('/jobs/{job}/apply', [ApplicationController::class, 'applyForJob']);

  // Apply for a project
  Route::post('/projects/{project}/apply', [ApplicationController::class, 'applyForProject']);
});
