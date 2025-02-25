<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTalent_profileRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SwifthayajobResource;
use App\Http\Resources\TalentProfileResource;
use App\Http\Resources\UserResource;
use App\Models\Project;
use App\Models\Swifthayajob;
use App\Models\Talent_profile;
use App\Models\User_profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TalentProfileController extends Controller
{
  //  Talent Profiles search and filter
  public function index(Request $request)
  {
    try {
      $query = Talent_profile::with("userprofile")->where('status', "approved");

      if (!empty($request->keyword)) {
        $query->where(function ($q) use ($request) {
          $q->orWhere('skills', 'like', '%' . $request->keyword . '%');
          $q->orWhere('skills', 'like', '%' . $request->keyword . '%');
          $q->orWhere('education', 'like', '%' . $request->keyword . '%');
          $q->orWhere('experience', 'like', '%' . $request->keyword . '%');
        });
      }


      // Filtering by skills
      if ($request->filled('skills')) {
        $skills = $request->input('skills');
        $query->where(function ($q) use ($skills) {
          $q->where('skills', 'like', '%' . $skills . '%');
        });
      }


      // Filtering by location (location is in user_profile table)
      if ($request->filled('location')) {
        $location = $request->input('location');
        $query->whereHas('userprofile', function ($q) use ($location) {
          $q->where('location', 'like', '%' . $location . '%');
        });
      }

      // Filtering by experience level
      if ($request->filled('experience')) {

        $experience = strval(request()->input('experience'));

        // $query->whereRaw("JSON_CONTAINS(experience, '\"" . $experience . "\"', '$[*].duration')");

        $query->whereJsonContains('experience', ['duration' => (string)$experience]);
      }

      // test
      // // Filtering by experience level
      // if ($request->filled('experience')) {
      //   $experience = intval($request->input('experience'));

      //   // Option 1: Using whereJsonContains for exact match
      //   $query->whereRaw("JSON_CONTAINS(experience, '\"" . $experience . "\"', '$[*].duration')");

      //   // Option 2: Using whereJsonContains with array structure
      //   $query->whereJsonContains('experience', ['duration' => (string)$experience]);
      // }

      // test

      $talents = $query->latest()->paginate(10);

      return TalentProfileResource::collection($talents);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to fetch Talents', 'error' => $e->getMessage()], 500);
    }
  }

  public function show(Talent_profile $talent_profile)
  {
    try {
      Gate::authorize("view", $talent_profile);
      $talent_profile->load("userprofile");
      return response()->json([
        'status' => 'success',
        'message' => 'Talent Profile fetched successfully',
        'data' => new TalentProfileResource($talent_profile),
      ]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(
        [
          'status' => 'error',
          'code' => 500,
          'message' => 'Failed to fetch Talent Profile'
        ],
        500
      );
    }
  }

  // public function store(StoreTalent_profileRequest $request)
  // {
  //   DB::beginTransaction(); // Begin DB transaction

  //   try {
  //     $user_profile = Auth::user()->userprofile;
  //     $has_talent_profile = Talent_profile::where("user_profile_id", $user_profile->id)->first();

  //     // check if user already has Talent profile
  //     if (!is_null($has_talent_profile)) {
  //       Gate::authorize("update", $has_talent_profile);
  //       DB::commit(); // Commit if no changes are required

  //       return response()->json([
  //         'message' => "User already has a Talent Profile",
  //       ], 409);
  //     }


  //     $validated = $request->validated();

  //     // create talent profile
  //     $talent_profile = $user_profile->talentprofile()->create([
  //       'user_profile_id' => $user_profile->id,
  //       'skills' => $validated["skills"],
  //       'experience' => $validated["experience"],
  //       'education' => $validated["education"],
  //       'portfolio' => $validated["portfolio"]
  //     ]);

  //     $talent_profile->refresh(); // Reload the model to get the default values (e.g., pending status)

  //     DB::commit(); // Commit transaction on success

  //     return [
  //       'message' => "Talent profile created successfully",
  //       "data" => new TalentProfileResource($talent_profile)
  //     ];
  //   } catch (Exception $e) {
  //     DB::rollBack(); // Rollback in case of failure
  //     return response()->json(['message' => 'Failed to create Talent Profile', 'error' => $e->getMessage()], 500);
  //   }
  // }



  public function update(StoreTalent_profileRequest $request, User_profile $user_profile)
  {
    Gate::authorize("update", $user_profile);

    DB::beginTransaction(); // Begin DB transaction

    try {
      $validated = $request->validated();

      $talent_profile = $user_profile->talentprofile;

      // update user profile
      $user_profile->update([
        'first_name' => $validated["first_name"],
        'last_name' => $validated["last_name"]
      ]);

      if ($talent_profile) {
        // Update existing talent profile
        $talent_profile->update($validated);
      }


      // create talent profile
      $user_profile->talentprofile()->create($validated);


      DB::commit(); // Commit transaction on success

      return response()->json([
        'status' => 'success',
        'message' => "Talent Profile updated successfully",
        "data" => new TalentProfileResource($talent_profile)
      ]);
    } catch (Exception $e) {
      Log::channel('api')->error($e->getMessage());
      DB::rollBack(); // Rollback on failure
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to update Talent Profile',
      ], 500);
    }
  }

  public function uploadPhoto(Request $request, Talent_profile $talent_profile)
  {
    Gate::authorize('update', $talent_profile);

    // Validate the uploaded profile_pic
    $request->validate([
      'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);


    // Delete the existing profile_pic file if it exists
    if ($request->has("profile_pic")) {
      // stor file in public folder
      $imagePath = $request->file("profile_pic")->store("talentProfilePic", "public");

      $validated = $imagePath;

      // deleting previous image to store new one 
      Storage::disk("public")->delete($talent_profile->profile_pic ?? "");

      $talent_profile->profile_pic = $validated;
      $talent_profile->update(["profile_pic" => $validated]);
    }

    return response()->json([
      'status' => 'success',
      'message' => "Talent's profile picture uploaded successfully",
      'data' => $imagePath,
    ]);
  }

  public function uploadResume(Request $request, Talent_profile $talent_profile)
  {
    // Authorize the user to update this talent profile
    Gate::authorize('update', $talent_profile);
    DB::beginTransaction(); // Begin DB transaction

    try {
      // Validate the incoming resume file
      $validated = $request->validate([
        'resume' => 'required|file|mimes:pdf,doc,docx|max:2048', // Adjust file types and size as needed
      ]);

      // Delete the existing resume file if it exists
      if ($request->has("resume")) {
        // stor file in public folder
        $imagePath = $request->file("resume")->store("resume", "public");

        $validated = $imagePath;

        // deleting previous image to store new one 
        Storage::disk("public")->delete($talent_profile->resume ?? "");

        $talent_profile->resume = $validated;
        $talent_profile->update(["resume" => $validated]);
      }

      DB::commit(); // Commit transaction on success

      return response()->json([
        'status' => 'success',
        'message' => 'Resume uploaded successfully',
        'data' => new TalentProfileResource($talent_profile)
      ]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on failure
      Log::channel('api')->error("resume upload", ["error", $e->getMessage()]);
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to upload resume',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  // public function destroy(Talent_profile $talent_profile)
  // {
  //   Gate::authorize("delete", $talent_profile);

  //   DB::beginTransaction(); // Begin DB transaction

  //   try {
  //     $talent_profile->delete();
  //     $talent_profile->userprofile->user->delete();
  //     DB::commit(); // Commit transaction on success

  //     return response()->json([
  //       "message"  => "Talent Profile deleted successfully"
  //     ]);
  //   } catch (Exception $e) {
  //     DB::rollBack(); // Rollback in case of failure
  //     return response()->json(['message' => 'Failed to delete Talent Profile', 'error' => $e->getMessage()], 500);
  //   }
  // }
}
