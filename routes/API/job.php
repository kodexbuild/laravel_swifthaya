<?php

use App\Http\Controllers\API\V1\SwifthayajobController;
use App\Http\Controllers\API\V1\TrackingController;
use Illuminate\Support\Facades\Route;


// job search
Route::get("/jobs/search", [SwifthayajobController::class, "job_search"])->middleware(['auth:sanctum', "can:talent"]);

Route::middleware(['auth:sanctum', 'can:employer'])->prefix("/jobs")->group(function () {

  // List all jobs
  Route::get('', [SwifthayajobController::class, 'index']);

  // Show a single job
  Route::get('/{job}', [SwifthayajobController::class, 'show']);

  // Create a new job (store)
  Route::post('/', [SwifthayajobController::class, 'store']);

  // Update an existing job
  Route::patch('/{job}', [SwifthayajobController::class, 'update']);

  // Delete a job
  Route::delete('/{job}', [SwifthayajobController::class, 'destroy']);
});



// job tracking
// Route::get('/job_tracking/pending', [TrackingController::class, 'pendingJob']);

// Route::patch('/job_tracking/{job}/complete', [TrackingController::class, 'completeJob']);

// Route::get('/job_tracking/{job}/start', [TrackingController::class, 'startJob']);
