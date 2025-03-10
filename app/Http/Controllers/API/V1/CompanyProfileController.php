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

  public function register(StoreCompany_profileRequest $request)
  {
    DB::beginTransaction(); // Start transaction

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
          'city' => $validated["city"],
          'state' => $validated["state"],
          "phone_number" => $validated["phone_number"],
        ]);


        // Create the company profile
        $company_profile = $user_profile->companyprofile()->create([
          'user_profile_id' => $user_profile->id,
          'company_name' => $validated["company_name"],
          'company_email' => $validated["company_email"],
          'company_phone_number' => $validated["company_phone_number"],
          'company_website' => $validated["company_website"],
          'city' => $validated["company_city"],
          'state' => $validated["company_state"],
          'industry' => $validated["industry"],
        ]);
      }

      // Create an API token for the user
      $token = $user->createToken('API Token')->plainTextToken;
      $company_profile->refresh(); // Reload to get the default values
      $company_profile->load("userprofile");


      DB::commit(); // Commit transaction

      return response()->json([
        "status" => "success",
        'message' => 'Employer(individual) registered successfully',
        "data" => new CompanyProfileResource($company_profile),
        "token" => $token
      ], 201); // 201 Created

      throw new Exception('User registration failed');
    } catch (Exception $e) {
      Log::channel('api')->error("debuggu", ["error" => $e->getMessage()]);
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'error' => [
          'code' => 'SERVER_ERROR',
          'message' => 'User registration failed',
        ]
      ], 500);
    }
  }
  // Show company profile
  public function show(Company_profile $company_profile)
  {
    Gate::authorize("modify", $company_profile);

    return response()->json([
      "status" => "success",
      "message" => "Company profile retrieved successfully",
      "data" => new CompanyProfileResource($company_profile)
    ], 200); // 200 OK

  }

  // Store company profile
  public function update(Company_profile $company_profile, StoreCompany_profileRequest $request)
  {
    DB::beginTransaction(); // Begin transaction

    Gate::authorize("modify", $company_profile);

    try {

      // Validate the request data
      $validated = $request->validated();
      // return $validated;
      $user = $company_profile->userprofile->user;
      $user_profile = $company_profile->userprofile;
      // Create the user
      if (User::where('email', $validated['email'])->where('id', '!=', $user_profile->user->id)->exists()) {
        return response()->json([
          'status' => 'error',
          'code' => 409,
          'message' => 'Email is already in use by another user',
        ], 409);
      }
      $user_profile->user()->update([
        'email' => $validated["email"],
      ]);

      // Create the user profile if user creation is successful
      if ($user) {
        $user_profile = $user->userprofile()->update([
          'first_name' => $validated["first_name"],
          'last_name' => $validated["last_name"],
          'city' => $validated["city"],
          'state' => $validated["state"],
          "phone_number" => $validated["phone_number"],
        ]);


        // Update the company profile
        $company_profile->update([
          'company_name' => $validated["company_name"],
          'company_email' => $validated["company_email"],
          'company_phone_number' => $validated["company_phone_number"],
          'company_website' => $validated["company_website"],
          'city' => $validated["company_city"],
          'state' => $validated["company_state"],
          'industry' => $validated["industry"],
        ]);
      }

      // Create an API token for the user
      $company_profile->refresh(); // Reload to get the default values
      $company_profile->load("userprofile");


      DB::commit(); // Commit transaction

      return response()->json([
        "status" => "success",
        'message' => 'Profile Updated successfully',
        "data" => new CompanyProfileResource($company_profile),
      ], 200); // 200
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
      'company_logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);


    // Delete the existing company_logo file if it exists
    if ($request->has("company_logo")) {
      // store file in public folder
      $imagePath = $request->file("company_logo")->store("profile_picture", "public");

      $validated = $imagePath;

      // deleting previous image to store new one 
      Storage::disk("public")->delete($company_profile->userprofile->profile_picture ?? "");

      $company_profile->userprofile->profile_picture = $validated;
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
