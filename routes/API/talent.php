<?php

use App\Http\Controllers\API\V1\TalentProfileController;
use Illuminate\Support\Facades\Route;


// View all talent profiles with filters 
Route::get("/talents/search", [TalentProfileController::class, "index"])->middleware(["auth:sanctum", "can:individual_company"]);

Route::middleware(['auth:sanctum', "can:talent", "throttle:api"])->prefix("/talents/profile")->group(function () {
  // View a talent profile 
  Route::get("/", [TalentProfileController::class, "show"]);

  // Create a new talent profile (store)
  Route::post("/", [TalentProfileController::class, "store"]);

  // Update an existing talent profile
  Route::patch("/", [TalentProfileController::class, "update"]);

  // Delete a talent profile
  // Route::delete("/{talent_profile}", [TalentProfileController::class, "destroy"]);
});
