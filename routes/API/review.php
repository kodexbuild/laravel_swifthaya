<?php

use App\Http\Controllers\API\V1\ReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix("/reviews")->group(function () {

  // reviews written by the authenticated user
  Route::get('/reviewed', [ReviewController::class, "reviewer"]);

  // reviews that were recieved by the authenticated user
  Route::get('/reviewers', [ReviewController::class, "reviewee"]);
  
  // single review
  Route::get('/{review}', [ReviewController::class, "show"]);
  // create a review
  Route::post('/', [ReviewController::class, "store"]);

  // update a review
  Route::patch('/{review}/', [ReviewController::class, "update"]);

  // delete a review
  Route::delete("/{review}/", [ReviewController::class, "destroy"]);
});
