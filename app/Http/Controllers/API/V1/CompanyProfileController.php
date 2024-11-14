<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompany_profileRequest;
use App\Http\Requests\UpdateCompany_profileRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Models\Company_profile;
use App\Models\User;
use App\Models\User_profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Add logging
use Exception;
use Illuminate\Support\Facades\Hash;

class CompanyProfileController extends Controller
{
  // Show company profile
  public function show()
  {
    try {
      $company_profile = Auth::user()->userprofile->companyprofile;
      if (!$company_profile) {
        // Return a 404 status for not found
        return response()->json(["message" => "User has no company profile"], 404);
      }

      Gate::authorize("modify", $company_profile);

      return new CompanyProfileResource($company_profile);
    } catch (Exception $e) {
      // Log the error for debugging
      Log::error('Error retrieving company profile', ['error' => $e->getMessage()]);
      return response()->json(['message' => 'Failed to fetch Company Profile', 'error' => $e->getMessage()], 500);
    }
  }

  // Store company profile
  public function store(StoreCompany_profileRequest $request)
  {
    DB::beginTransaction(); // Begin transaction

    try {

      // Validate the request data
      $validated = $request->validated();
      // Create the user
      $user = User::create([
        'email' => $validated["email"],
        'password' => Hash::make($validated["password"]),
        'user_type' => "company",
      ]);

      // Create the user profile if user creation is successful
      if ($user) {
        $user_profile = $user->userprofile()->create([
          'user_id' => $user->id,
          'first_name' => $validated["first_name"],
          'last_name' => $validated["last_name"],
          "bio" => $validated["bio"],
          "location" => $validated["location"],
          "phone_number" => $validated["phone_number"],
          "website" => $validated["website"]
        ]);


        // Create the company profile
        $company_profile = $user_profile->companyprofile()->create([
          'user_profile_id' => $user_profile->id,
          'company_name' => $validated["company_name"],
          'industry' => $validated["industry"],
          'company_size' => $validated["company_size"],
          'founded_year' => $validated["founded_year"],
        ]);
      }

      // Create an API token for the user
      $token = $user->createToken('API Token')->plainTextToken;
      $company_profile->refresh(); // Reload to get the default values
      $company_profile->load("userprofile");


      DB::commit(); // Commit transaction

      return response()->json([
        "message" => "Company profile created successfully",
        "data" => new CompanyProfileResource($company_profile),
        "token" => $token
      ], 201); // 201 Created
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on error
      Log::error('Error creating company profile', ['error' => $e->getMessage()]);
      return response()->json(['message' => 'Failed to create company profile', 'error' => $e->getMessage()], 500);
    }
  }

  // Update company profile
  public function update(UpdateCompany_profileRequest $request)
  {
    DB::beginTransaction(); // Begin transaction

    try {
      $validated = $request->validated();
      $company = Auth::user()->userprofile->companyprofile;
      // Update the company profile
      $company->update([
        'company_name' => $validated["company_name"] ?? $company->company_name,
        'industry' => $validated["industry"] ?? $company->industry,
        'company_size' => $validated["company_size"] ?? $company->company_size,
        'founded_year' =>  $validated["founded_year"] ?? $company->founded_year,
      ]);

      DB::commit(); // Commit transaction

      return response()->json([
        "message" => "Company profile updated successfully",
        "data" => new CompanyProfileResource($company)
      ], 200); // 200 OK
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on error
      Log::error('Error updating company profile', ['error' => $e->getMessage()]);
      return response()->json(['message' => 'Failed to update company profile', 'error' => $e->getMessage()], 500);
    }
  }

  // Delete company profile
  public function destroy(Company_profile $company)
  {
    DB::beginTransaction(); // Begin transaction

    try {
      Gate::authorize("delete", $company);

      // Delete the company profile and associated user
      $company->delete();
      $company->userprofile->user->delete();

      DB::commit(); // Commit transaction

      return response()->json(["message" => "Company profile deleted successfully"], 200); // 200 OK
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on error
      Log::error('Error deleting company profile', ['error' => $e->getMessage()]);
      return response()->json(['message' => 'Failed to delete company profile', 'error' => $e->getMessage()], 500);
    }
  }
}
