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
use Illuminate\Support\Facades\Storage;

class CompanyProfileController extends Controller
{
  // Show company profile
  public function show(Company_profile $company_profile)
  {
    try {
      Gate::authorize("modify", $company_profile);

      return response()->json([
        "status" => "success",
        "message" => "Company profile retrieved successfully",
        "data" => new CompanyProfileResource($company_profile)
      ], 200); // 200 OK
    } catch (Exception $e) {
      // Log the error for debugging
      Log::error('Error retrieving company profile', ['error' => $e->getMessage()]);

      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to retrieve company profile',
      ], 500);
    }
  }

  // Store company profile
  public function update(Company_profile $company_profile, StoreCompany_profileRequest $request)
  {
    DB::beginTransaction(); // Begin transaction

    try {

      // Validate the request data
      $validated = $request->validated();
      // return $validated;
      $user = $company_profile->userprofile->user;
      // Create the user
      $user->update([
        'email' => $validated["email"],
        'password' => Hash::make($validated["password"]),
      ]);

      // Create the user profile if user creation is successful
      if ($user) {
        $user_profile = $user->userprofile()->update([
          'street_address' => $validated["street_address"],
          'city' => $validated["city"],
          'state' => $validated["state"],
          "phone_number" => $validated["phone_number"],
          "bio" => $validated["bio"],
        ]);


        // Create the company profile
        $company_profile->update([
          'company_name' => $validated["company_name"],
          'company_slogan' => $validated["company_slogan"],
          'company_size' => $validated["company_size"],
          'company_website' => $validated["company_website"],
          'founded_year' => $validated["founded_year"] ?? $company_profile->founded_year,
          'industry' => $validated["industry"],
          'linkedin_url' => $validated["linkedin_url"],
          'github_url' => $validated["github_url"],
          'twitter_url' => $validated["twitter_url"],
          'instagram_url' => $validated["instagram_url"]
        ]);
      }

      // Create an API token for the user
      $token = $user->createToken('API Token')->plainTextToken;
      $company_profile->refresh(); // Reload to get the default values
      $company_profile->load("userprofile");


      DB::commit(); // Commit transaction

      return response()->json([
        "status" => "success",
        "message" => "Company profile updated successfully",
        "data" => new CompanyProfileResource($company_profile),
        "token" => $token
      ], 201); // 201 Created
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on error
      // Log the error for debugging
      Log::channel("api")->error('Error updating company profile', ['error' => $e->getMessage()]);

      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to update company profile',
      ], 500);
    }
  }

  /**
   * Upload company logo.
   */
  public function uploadLogo(Request $request, Company_profile $company_profile)
  {
    Gate::authorize('update', $company_profile);

    // Validate the uploaded profile_picture
    $request->validate([
      'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);


    // Delete the existing profile_picture file if it exists
    if ($request->has("profile_picture")) {
      // stor file in public folder
      $imagePath = $request->file("profile_picture")->store("profile_picture", "public");

      $validated = $imagePath;

      // deleting previous image to store new one 
      Storage::disk("public")->delete($company_profile->userprofile->profile_picture ?? "");

      $company_profile->profile_picture = $validated;
      $company_profile->userprofile()->update(["profile_picture" => $validated]);
    }
    $company_profile->refresh();

    return response()->json([
      'status' => 'success',
      'message' => 'Company logo uploaded successfully',
      'data' => new CompanyProfileResource($company_profile)
    ]);
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
