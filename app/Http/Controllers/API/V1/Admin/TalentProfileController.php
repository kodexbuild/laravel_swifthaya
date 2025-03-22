<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTalent_profileRequest;
use App\Http\Resources\TalentProfileResource;
use App\Models\Talent_profile;
use App\Models\User;
use App\Models\User_profile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TalentProfileController extends Controller
{
  // List all Talent Profiles
  public function index()
  {
    try {
      $talent_profiles = Talent_profile::with("userprofile")->paginate(10); // Use pagination to limit results
      return TalentProfileResource::collection($talent_profiles)
        ->response()
        ->setStatusCode(200); // Return a collection of Talent Profiles
    } catch (Exception $e) {
      return response()->json([
        "status" => "error",
        "code" => 500,
        'message' => 'Failed to retrieve Talent Profiles',
      ], 500); // Handle failure case
    }
  }

  public function count()
  {
    // Get the count of all Talent Profiles
    $talent_profiles_count = Talent_profile::count();
    return response()->json([
      "status" => "success",
      "message" => "Talent profile count retrieved successfully.",
      "data" => [
        "count" => $talent_profiles_count
      ]
    ]);
  }

  // Show a specific Talent Profile
  public function show(Talent_profile $talent_profile)
  {
    Gate::authorize("view", $talent_profile); // Check if the user has permission to view

    return response()->json([
      "status" => "success",
      "message" => "Talent profile retrieved successfully.",
      "data" => new TalentProfileResource($talent_profile)
    ]);
  }

  // // Delete a Talent Profile
  // public function destroy(Talent_profile $talent_profile)
  // {
  //   Gate::authorize("delete", $talent_profile); // Check if the user is authorized to delete the profile

  //   DB::beginTransaction(); // Begin DB transaction

  //   try {
  //     $talent_profile->delete(); // Delete the talent profile
  //     $talent_profile->userprofile->user->delete(); // Also delete the associated user (risky, double-check this logic!)
  //     DB::commit(); // Commit transaction after successful deletion

  //     return response()->json([
  //       "message"  => "Talent Profile deleted successfully"
  //     ]);
  //   } catch (Exception $e) {
  //     DB::rollBack(); // Rollback in case of failure
  //     return response()->json(['message' => 'Failed to delete Talent Profile', 'error' => $e->getMessage()], 500);
  //   }
  // }

  // Approve a Talent Profile
  public function approve(Talent_profile $talent_profile)
  {
    DB::beginTransaction(); // Begin DB transaction

    try {

      if ($talent_profile->status === 'approved') {
        return response()->json([
          'status' => 'error',
          'message' => 'Talent profile has already been approved.',
        ], 400);
      }
      $talent_profile->status = 'approved'; // Set status to 'approved'
      $talent_profile->save(); // Save the changes

      DB::commit(); // Commit transaction after successful approval

      $talent_profile->refresh();

      return response()->json([
        'status' => 'success',
        'message' => 'Talent profile approved successfully.',
        "data" => new TalentProfileResource($talent_profile)
      ], 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json([
        "status" => "error",
        "code" => 500,
        'message' => 'Failed to approve talent profile',
      ], 500);
    }
  }

  // Reject a Talent Profile
  public function reject(Talent_profile $talent_profile)
  {
    DB::beginTransaction(); // Begin DB transaction

    try {
      if ($talent_profile->status === 'rejected') {
        return response()->json([
          'status' => 'error',
          'message' => 'Talent profile has already been rejected.',
        ], 400);
      }
      $talent_profile->status = 'rejected'; // Set status to 'rejected'

      $talent_profile->save(); // Save the changes

      DB::commit(); // Commit transaction after successful rejection

      $talent_profile->refresh();

      return response()->json([
        'status' => 'success',
        'message' => 'Talent profile rejected successfully.',
        "data" => new TalentProfileResource($talent_profile)
      ], 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to reject talent profile',
      ], 500);
    }
  }
}
