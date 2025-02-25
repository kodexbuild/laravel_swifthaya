<?php

use App\Http\Controllers\API\V1\TalentProfileController;
use Illuminate\Support\Facades\Route;


// View all talent profiles with filters 
Route::get("/talents/search", [TalentProfileController::class, "index"])->middleware(["auth:sanctum", "can:individual_company"]);

Route::middleware(['auth:sanctum', "can:talent"])->prefix("/talents")->group(function () {
  // View a talent profile 
  Route::get("/{talent_profile}", [TalentProfileController::class, "show"]);

  // Create a new talent profile (store)
  // Route::post("/", [TalentProfileController::class, "store"]);

  // Update an existing talent profile
  Route::patch("/{user_profile}", [TalentProfileController::class, "update"]);

  Route::post('/{talent_profile}/resume', [TalentProfileController::class, 'uploadResume']);
  Route::post('/{talent_profile}/profile_pic', [TalentProfileController::class, 'uploadResume']);
  

  // Delete a talent profile
  // Route::delete("/{talent_profile}", [TalentProfileController::class, "destroy"]);
});
