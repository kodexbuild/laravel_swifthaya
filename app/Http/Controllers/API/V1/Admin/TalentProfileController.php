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
      return TalentProfileResource::collection($talent_profiles); // Return paginated results as resource
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to retrieve Talent Profiles', 'error' => $e->getMessage()], 500); // Handle failure case
    }
  }

  public function count()
  {
    try {
      $talent_profiles_count = Talent_profile::count();
      return response()->json([
        "message" => "Talent profile count retrieved successfully.",
        "data" => [
          "count" => $talent_profiles_count
        ]
      ]);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to retrieve talent profile count', 'error' => $e->getMessage()], 500); // Handle failure case
    }
  }

  // Show a specific Talent Profile
  public function show(Talent_profile $talent_profile)
  {
    Gate::authorize("view", $talent_profile); // Check if the user has permission to view
    try {
      return new TalentProfileResource($talent_profile); // Return profile as resource
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to retrieve Talent Profile', 'error' => $e->getMessage()], 500);
    }
  }

  // Store a new Talent Profile
  public function store(StoreTalent_profileRequest $request, User $user)
  {
    DB::beginTransaction(); // Begin DB transaction to maintain data consistency

    try {
      $user_profile = $user->userprofile; // Get the associated user profile
      $has_talent_profile = Talent_profile::where("user_profile_id", $user_profile->id)->first(); // Check if the user already has a talent profile

      if (!is_null($has_talent_profile)) { // If a profile exists
        Gate::authorize("update", $has_talent_profile); // Authorize user to update the profile
        DB::commit(); // No changes needed, commit the transaction

        return [
          'message' => "User already has a talent profile",
          new TalentProfileResource($has_talent_profile) // Return existing profile
        ];
      }

      $validated = $request->validated(); // Validate the request

      // Convert comma-separated strings to arrays for skills, experience, education, and portfolio
      $skillsArray = explode(',', $validated["skills"]);
      $experienceArray = $validated["experience"];
      $educationArray = $validated["education"];
      $portfolioArray = $validated["portfolio"];

      // Create new talent profile
      $talent_profile = $user_profile->talentprofile()->create([
        'user_profile_id' => $user_profile->id,
        'skills' => json_encode($skillsArray),
        'experience' => json_encode($experienceArray),
        'education' => json_encode($educationArray),
        'portfolio' => json_encode($portfolioArray)
      ]);

      DB::commit(); // Commit transaction after successful creation

      return new TalentProfileResource($talent_profile); // Return created profile
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to create Talent Profile', 'error' => $e->getMessage()], 500);
    }
  }

  // Update a Talent Profile
  public function update(StoreTalent_profileRequest $request, Talent_profile $talent_profile)
  {
    Gate::authorize("update", $talent_profile); // Check if the user is authorized to update the profile

    DB::beginTransaction(); // Begin DB transaction

    try {
      $validated = $request->validated(); // Validate the request

      // Convert comma-separated strings to arrays
      $skillsArray = explode(',', $validated["skills"]);
      $experienceArray = $validated["experience"];
      $educationArray = $validated["education"];
      $portfolioArray = $validated["portfolio"];

      // Update the talent profile
      $talent_profile->update([
        'skills' => json_encode($skillsArray),
        'experience' => json_encode($experienceArray),
        'education' => json_encode($educationArray),
        'portfolio' => json_encode($portfolioArray)
      ]);

      DB::commit(); // Commit transaction after successful update

      return new TalentProfileResource($talent_profile); // Return updated profile
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on failure
      return response()->json(['message' => 'Failed to update Talent Profile', 'error' => $e->getMessage()], 500);
    }
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
      $talent_profile->status = 'approved'; // Set status to 'approved'
      $talent_profile->save(); // Save the changes
      DB::commit(); // Commit transaction after successful approval
      $talent_profile->refresh();
      return response()->json(['message' => 'Talent profile approved successfully.', "data" => new TalentProfileResource($talent_profile)], 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to approve talent profile', 'error' => $e->getMessage()], 500);
    }
  }

  // Reject a Talent Profile
  public function reject(Talent_profile $talent_profile)
  {
    DB::beginTransaction(); // Begin DB transaction

    try {
      $talent_profile->status = 'rejected'; // Set status to 'rejected'
      $talent_profile->save(); // Save the changes
      DB::commit(); // Commit transaction after successful rejection

      $talent_profile->refresh();
      return response()->json(['message' => 'Talent profile rejected successfully.', "data" => new TalentProfileResource($talent_profile)], 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to reject talent profile', 'error' => $e->getMessage()], 500);
    }
  }
}
