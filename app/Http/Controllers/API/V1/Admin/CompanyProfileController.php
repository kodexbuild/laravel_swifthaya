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
use Illuminate\Support\Facades\Log;

class CompanyProfileController extends Controller
{
  // List all Company Profiles
  public function index()
  {
    try {
      // Retrieve all company profiles with pagination
      $company_profiles = Company_profile::latest()->paginate(10);

      // Return a collection of company profiles using resource formatting
      return CompanyProfileResource::collection($company_profiles)
        ->response()
        ->setStatusCode(200);;
    } catch (Exception $e) {
      // Handle errors and return an error response
      return response()->json([
        "status" => "error",
        "code" => 500,
        'message' => 'Failed to retrieve Company profiles',

      ], 500);
    }
  }

  // Get the total no of Company Profiles
  public function count()
  {
    try {
      // Retrieve all company profiles with pagination
      $company_profile_count = Company_profile::count();

      return response()->json([
        "status" => "success",
        "message" => "Company profile count retrieved successfully.",
        "data" => [
          "count" => $company_profile_count
        ]
      ]);
    } catch (Exception $e) {
      // Handle errors and return an error response
      return response()->json([
        "status" => "error",
        "code" => 500,
        'message' => 'Failed to retrieve Company profile count',
      ], 500);
    }
  }

  // View a specific Company Profile
  public function show(Company_profile $company)
  {

    $company->load("userprofile");
    // Return the specific company profile using a resource
    return response()->json([
      "status" => "success",
      "message" => "Company profile retrieved successfully.",
      "data" => new CompanyProfileResource($company)
    ]);
  }


  // Approve a company profile
  public function approve(Company_profile $company_profile)
  {
    try {
      DB::beginTransaction();
      if ($company_profile->status === 'approved') {
        return response()->json([
          "status" => "error",
          'message' => 'Company profile has already been approved.',
        ], 400);
      }
      // Set the company_profile status to 'approved'
      $company_profile->status = 'approved';
      $company_profile->save();

      DB::commit();

      $company_profile->refresh();

      // Return success message
      return response()->json([
        'status' => 'success',
        'message' => 'Company profile has been approved.',
        "data" => new CompanyProfileResource($company_profile)
      ], 200);
    } catch (Exception $e) {
      DB::rollBack();
      Log::channel("api")->error("Failed to approve Company profile", ["error" => $e->getMessage()]);
      return response()->json([
        "status" => "error",
        "code" => 500,
        'message' => 'Failed to approve Company profile',
      ], 500);
    }
  }

  // Reject a company profile
  public function reject(Company_profile $company_profile)
  {
    try {
      if ($company_profile->status === 'rejected') {
        return response()->json([
          "status" => "error",
          'message' => 'Company profile has already been rejected.',
        ], 400);
      }
      // Set the company_profile status to 'rejected'
      $company_profile->status = 'rejected';
      $company_profile->save();
      $company_profile->refresh();

      // Return success message
      return response()->json([
        "status" => "success",
        'message' => 'Company profile has been rejected.',
        "data" => new CompanyProfileResource($company_profile)
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        "status" => "error",
        "code" => 500,
        'message' => 'Failed to reject Company profile',
      ], 500);
    }
  }
}
