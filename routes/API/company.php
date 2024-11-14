<?php

use App\Http\Controllers\API\V1\CompanyProfileController;
use Illuminate\Support\Facades\Route;

Route::post("/register/company", [CompanyProfileController::class, "store"]);

Route::middleware(['auth:sanctum', "can:company"])->prefix("/companies/profile")->group(function () {

  // View a single company profile
  Route::get("/", [CompanyProfileController::class, "show"]);

  // Create a new company profile

  // Update an existing company profile
  Route::patch("/", [CompanyProfileController::class, "update"]);

  // Delete a company profile
  // Route::delete("/{company}", [CompanyProfileController::class, "destroy"]);
});
