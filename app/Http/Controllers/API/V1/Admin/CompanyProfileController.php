<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompany_profileRequest;
use App\Http\Requests\UpdateCompany_profileRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Models\Company_profile;
use App\Models\User;
use App\Models\User_profile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyProfileController extends Controller
{
  // List all Company Profiles
  public function index()
  {
    try {
      // Retrieve all company profiles with pagination
      $company_profiles = Company_profile::latest()->paginate(10);

      // Return a collection of company profiles using resource formatting
      return CompanyProfileResource::collection($company_profiles);
    } catch (Exception $e) {
      // Handle errors and return an error response
      return response()->json(['message' => 'Failed to retrieve Company profiles', 'error' => $e->getMessage()], 500);
    }
  }

  // Get the total no of Company Profiles
  public function count()
  {
    try {
      // Retrieve all company profiles with pagination
      $company_profile_count = Company_profile::count();

      return response()->json([
        "message" => "Company profile count retrieved successfully.",
        "data" => [
          "count" => $company_profile_count
        ]
      ]);
    } catch (Exception $e) {
      // Handle errors and return an error response
      return response()->json(['message' => 'Failed to retrieve Company profile count', 'error' => $e->getMessage()], 500);
    }
  }

  // View a specific Company Profile
  public function show(Company_profile $company)
  {
    try {
      $company->load("userprofile");
      // Return the specific company profile using a resource
      return new CompanyProfileResource($company);
    } catch (Exception $e) {
      // Handle errors and return an error response
      return response()->json(['message' => 'Failed to retrieve company profile', 'error' => $e->getMessage()], 500);
    }
  }

  // Store a new Company Profile for a given user
  // public function store(StoreCompany_profileRequest $request)
  // {
  //   DB::beginTransaction(); // Begin transaction for database consistency

  //   try {
  //     $user = Auth::user();
  //     // Validate the request data
  //     $validated = $request->validated();

  //     // Check if the user already has a company profile
  //     $has_company_profile = Company_profile::where("user_profile_id", $user->userprofile->id)->first();

  //     if (!is_null($has_company_profile)) {
  //       // If the user already has a company profile, return a response
  //       return [
  //         'message' => "User already has a company profile",
  //         new CompanyProfileResource($has_company_profile)
  //       ];
  //     }

  //     // Create a new company profile for the user
  //     $company_profile = $user->userprofile->companyprofile()->create([
  //       'user_profile_id' => $user->userprofile->id, // Link company profile to the user profile
  //       'company_name' => $validated["company_name"],
  //       'industry' => $validated["industry"],
  //       'company_size' => $validated["company_size"],
  //       'founded_year' => $validated["founded_year"],
  //     ]);

  //     DB::commit(); // Commit transaction

  //     // Return the newly created company profile resource
  //     return new CompanyProfileResource($company_profile);
  //   } catch (Exception $e) {
  //     DB::rollBack(); // Rollback transaction if any error occurs
  //     return response()->json(['message' => 'Failed to create company profile', 'error' => $e->getMessage()], 500);
  //   }
  // }

  // // Update an existing company profile
  // public function update(UpdateCompany_profileRequest $request)
  // {
  //   DB::beginTransaction(); // Begin transaction for consistency

  //   try {
  //     $company_profile = Auth::user()->userprofile->company_profile;
  //     // Validate the incoming request data
  //     $validated = $request->validated();

  //     // Update the company profile with new data
  //     $company_profile->update([
  //       'company_name' => $validated["company_name"],
  //       'industry' => $validated["industry"],
  //       'company_size' => $validated["company_size"],
  //       'founded_year' => $validated["founded_year"],
  //     ]);

  //     DB::commit(); // Commit transaction

  //     // Return the updated company profile resource
  //     return new CompanyProfileResource($company_profile);
  //   } catch (Exception $e) {
  //     DB::rollBack(); // Rollback transaction if an error occurs
  //     return response()->json(['message' => 'Failed to update company profile', 'error' => $e->getMessage()], 500);
  //   }
  // }


  // Delete a company profile ....no longer in use
  // public function destroy(Company_profile $company_profile)
  // {
  //   DB::beginTransaction(); // Begin transaction for consistency

  //   try {
  //     // Delete the company profile and associated user account
  //     $company_profile->delete();
  //     $company_profile->userprofile->user->delete(); // Deletes the user associated with the profile

  //     DB::commit(); // Commit transaction

  //     // Return a success message
  //     return response()->json(["message" => "Company profile deleted successfully"]);
  //   } catch (Exception $e) {
  //     DB::rollBack(); // Rollback transaction if an error occurs
  //     return response()->json(['message' => 'Failed to delete company profile', 'error' => $e->getMessage()], 500);
  //   }
  // }

  // Approve a company profile
  public function approve(Company_profile $company_profile)
  {
    try {
      // Set the company_profile status to 'approved'
      $company_profile->status = 'approved';
      $company_profile->save();
      $company_profile->refresh();

      // Return success message
      return response()->json(['message' => 'Company profile has been approved.', "data" => new CompanyProfileResource($company_profile)], 200);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to approve Company profile', 'error' => $e->getMessage()], 500);
    }
  }

  // Reject a company profile
  public function reject(Company_profile $company_profile)
  {
    try {
      // Set the company_profile status to 'rejected'
      $company_profile->status = 'rejected';
      $company_profile->save();
      $company_profile->refresh();

      // Return success message
      return response()->json(['message' => 'Company profile has been rejected.', "data" => new CompanyProfileResource($company_profile)], 200);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to reject Company profile', 'error' => $e->getMessage()], 500);
    }
  }
}
