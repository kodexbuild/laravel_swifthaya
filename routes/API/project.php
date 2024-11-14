<?php

use App\Http\Controllers\API\V1\ProjectController;
use App\Http\Controllers\API\TrackingController;
use Illuminate\Support\Facades\Route;


// project search
Route::get("/projects/search", [ProjectController::class, "project_search"])->middleware(['auth:sanctum', "can:talent"]);

Route::middleware(['auth:sanctum', "can:individual_company"])->prefix("/projects")->group(function () {

  // List all projects
  Route::get("/", [ProjectController::class, "index"]);

  // View a single project (store)
  Route::get("/{project}", [ProjectController::class, "show"]);

  // Create a new project (store)
  Route::post("/", [ProjectController::class, "store"]);

  // Update an existing project
  Route::patch("/{project}/", [ProjectController::class, "update"]);

  // Delete an existing project
  Route::delete("/{project}/", [ProjectController::class, "destroy"]);
});


// Route::get('/project_tracking/pending', [TrackingController::class, 'pendingProject']);

// Route::patch('/project_tracking/{project}/complete', [TrackingController::class, 'completeProject']);

// Route::get('/project_tracking/{project}/start', [TrackingController::class, 'startProject']);
