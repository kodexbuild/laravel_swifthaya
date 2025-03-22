<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SwifthayajobResource;
use App\Http\Resources\UserResource;
use App\Models\Application;
use App\Models\Project;
use App\Models\Swifthayajob;
use App\Models\User;
use App\Models\User_profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
  // Method to apply for a project
  public function applyJob(Swifthayajob $job)
  {
    DB::beginTransaction(); // Start transaction to ensure data consistency

    try {
      $user = Auth::user();
      $user_profile = User_profile::where('user_id', $user->id)->first();
      $talent_profile = $user_profile->talentprofile;

      // Ensure user has a talent profile
      if (is_null($talent_profile)) {
        return response()->json([
          "status" => "error",
          "message" => "User does not have a talent profile",
        ], 400);
      }

      // Check if the user's and talent profile's status are approved
      if ($user->status !== 'approved' || $talent_profile->status !== 'approved') {
        return response()->json([
          "status" => "error",
          "message" => "User's profile has not been approved",
        ], 400);
      }

      // Ensure the user hasn't already applied for the job
      $has_applied_to_job = Application::where([
        'applicant_id' => $user->id,
        'swifthayajob_id' => $job->id,
      ])->exists();

      // return "good";
      if ($has_applied_to_job) {
        return response()->json([
          "status" => "error",
          "message" => "User has already applied for this job",
        ], 400);
      }


      // Create the application
      $application = $user->applications()->create([
        'applicant_id' => $user->id,
        'swifthayajob_id' => $job->id,
      ]);

      $application->refresh(); // Reload model to get any default values
      // Eager load the job details along with the application

      $application->load('swifthayajob');

      DB::commit(); // Commit transaction

      return response()->json([
        "status" => "success",
        'message' => 'Application successful',
        'data' => new ApplicationResource($application),
      ], 201);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        "status" => "error",
        "code" => 500,
        "message" => "Something went wrong on the server",
      ], 500);
    }
  }

  // Retrieve all jobs that the authenticated talent has applied to
  public function jobApplications()
  {
    try {
      $applicant_id = Auth::user()->id;

      // // Get all jobs the user has applied to
      // $jobs = Swifthayajob::whereHas('application', function ($query) use ($applicant_id) {
      //   $query->where('applicant_id', $applicant_id);
      // })->get();
      $application = Application::with("swifthayajob")->where("applicant_id", $applicant_id)->whereNotNull('swifthayajob_id')->latest()->paginate(10);


      return ApplicationResource::collection($application);
    } catch (Exception $e) {
      return response()->json([
        "status" => "error",
        "code" => 500,
        "message" => "Something went wrong on the server",
      ], 500);
    }
  }

  // Retrieve users that applied to a specific job
  public function viewJobApplicants($job_id)
  {
    try {
      // Fetch all users who applied to the given job

      $employer_id = Auth::user()->id;

      $applicants = Application::with("user")->where("swifthayajob_id", $job_id)->whereHas('swifthayajob', function ($query) use ($employer_id) {
        $query->where('employer_id', $employer_id);
      })->latest()->paginate(10);

      return ApplicationResource::collection($applicants);
    } catch (Exception $e) {
      return response()->json([
        "status" => "error",
        "code" => 500,
        "message" => "Something went wrong on the server",
      ], 500);
    }
  }

  // Accept an application
  public function accept(Application $application)
  {
    DB::beginTransaction(); // Start transaction

    try {
      Gate::authorize("modify", $application);
      if ($application->status === 'accepted') {
        return response()->json([
          "status" => "error",
          "code" => 400,
          'message' => 'Application has already been accepted.'
        ], 400);
      }
      // Update the application status to 'accepted'
      $application->status = 'accepted';
      $application->save();

      DB::commit(); // Commit transaction

      return response()->json(['message' => 'Application accepted successfully.', 'data' => new ApplicationResource($application)]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        "status" => "error",
        "code" => 500,
        "message" => "Something went wrong on the server",
      ], 500);
    }
  }

  // Shortlist an application
  public function shortlist(Application $application)
  {
    DB::beginTransaction(); // Start transaction

    try {
      Gate::authorize("modify", $application);
      // Update the application status to 'shortlisted'
      if ($application->status === 'shortlisted') {
        return response()->json([
          "status" => "error",
          "code" => 400,
          'message' => 'Application has already been shortlisted.'
        ], 400);
      }
      $application->status = 'shortlisted';
      $application->save();

      DB::commit(); // Commit transaction

      return response()->json(['message' => 'Application shortlisted successfully.', 'data' => new ApplicationResource($application)]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        "status" => "error",
        "code" => 500,
        "message" => "Something went wrong on the server",
      ], 500);
    }
  }

  // Reject an application
  public function reject(Application $application)
  {
    DB::beginTransaction(); // Start transaction

    try {
      Gate::authorize("modify", $application);
      if ($application->status === 'rejected') {
        return response()->json([
          "status" => "error",
          "code" => 400,
          'message' => 'Application has already been rejected.'
        ], 400);
      }
      // Update the application status to 'rejected'
      $application->status = 'rejected';
      $application->save();

      DB::commit(); // Commit transaction

      return response()->json(['message' => 'Application rejected successfully.', 'data' => new ApplicationResource($application)]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        "status" => "error",
        "code" => 500,
        "message" => "Something went wrong on the server",
      ], 500);
    }
  }
}
