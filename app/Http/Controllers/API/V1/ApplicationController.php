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
  public function applyForProject(Project $project)
  {m
    DB::beginTransaction(); // Start transaction to ensure data consistency

    try {
      $user = Auth::user();
      $user_profile = User_profile::where('user_id', $user->id)->first();
      $talent_profile = $user_profile->talentprofile;

      // Ensure user has a talent profile
      if (is_null($talent_profile)) {
        throw new Exception('User does not have a talent profile');
      }

      // Check if the user's and talent profile's status are approved
      if ($user->status !== 'approved' || $talent_profile->status !== 'approved') {
        throw new Exception("User's profile has not been approved");
      }

      // Ensure the user hasn't already applied for the project
      $has_applied_to_project = Application::where([
        'applicant_id' => $user->id,
        'project_id' => $project->id,
      ])->exists();

      if ($has_applied_to_project) {
        throw new Exception('User has already applied for this project');
      }

      // Create the application
      $application = $user->applications()->create([
        'applicant_id' => $user->id,
        'project_id' => $project->id,
      ]);

      $application->refresh(); // Reload model to get any default values

      // Eager load the job details along with the application
      $application->load('project');

      DB::commit(); // Commit transaction

      return response()->json([
        'message' => 'Application successful',
        'data' => new ApplicationResource($application),
      ], 201);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  // Method to apply for a job
  public function applyForJob(Swifthayajob $job)
  {
    DB::beginTransaction(); // Start transaction

    try {
      $user = Auth::user();
      $user_profile = User_profile::where('user_id', $user->id)->first();
      $talent_profile = $user_profile->talentprofile;

      // Ensure user has a talent profile
      if (is_null($talent_profile)) {
        throw new Exception('User does not have a talent profile');
      }

      // Check if the user's and talent profile's status are approved
      if ($user->status !== 'approved' || $talent_profile->status !== 'approved') {
        throw new Exception("User's profile has not been approved");
      }

      // Ensure the user hasn't already applied for the job
      $has_applied_to_job = Application::where([
        'applicant_id' => $user->id,
        'swifthayajob_id' => $job->id,
      ])->exists();

      if ($has_applied_to_job) {
        throw new Exception('User has already applied for this job');
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
        'message' => 'Application successful',
        'data' => new ApplicationResource($application),
      ], 201);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  // Retrieve all projects that the authenticated talent has applied to
  public function projectApplications()
  {
    try {
      $applicant_id = Auth::user()->id;

      // // Get all projects the user has applied to
      // $projects = Project::whereHas('application', function ($query) use ($applicant_id) {
      //   $query->where('applicant_id', $applicant_id);
      // })->get();
      $application = Application::with("project")->where("applicant_id", $applicant_id)->whereNotNull('project_id')->latest()->paginate(10);

      return ApplicationResource::collection($application);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
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
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  // Retrieve users that applied to a specific project
  public function viewProjectApplicants($project_id)
  {
    try {
      // // Fetch all users who applied to the given project
      // $applicants = User::whereHas('applications', function ($query) use ($project_id) {
      //   $query->where('project_id', $project_id);
      // })->get();

      $employer_id = Auth::user()->id;

      $applicants = Application::with("user")->where("project_id", $project_id)->whereHas('project', function ($query) use ($employer_id) {
        $query->where('poster_id', $employer_id);
      })->latest()->paginate(10);

      return ApplicationResource::collection($applicants);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  // Retrieve users that applied to a specific job
  public function viewJobApplicants($job_id)
  {
    try {
      // Fetch all users who applied to the given job
      // $applicants = User::whereHas('applications', function ($query) use ($job_id) {
      //   $query->where('swifthayajob_id', $job_id);
      // })->get();

      $employer_id = Auth::user()->id;

      $applicants = Application::with("user")->where("swifthayajob_id", $job_id)->whereHas('swifthayajob', function ($query) use ($employer_id) {
        $query->where('company_id', $employer_id);
      })->latest()->paginate(10);

      return ApplicationResource::collection($applicants);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  // Accept an application
  public function accept(Application $application)
  {
    DB::beginTransaction(); // Start transaction

    try {
      Gate::authorize("modify", $application);
      // Update the application status to 'accepted'
      $application->status = 'accepted';
      $application->save();

      DB::commit(); // Commit transaction

      return response()->json(['message' => 'Application accepted successfully.', 'data' => new ApplicationResource($application)]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  // Shortlist an application
  public function shortlist(Application $application)
  {
    DB::beginTransaction(); // Start transaction

    try {
      Gate::authorize("modify", $application);
      // Update the application status to 'shortlisted'
      $application->status = 'shortlisted';
      $application->save();

      DB::commit(); // Commit transaction

      return response()->json(['message' => 'Application shortlisted successfully.', 'data' => new ApplicationResource($application)]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  // Reject an application
  public function reject(Application $application)
  {
    DB::beginTransaction(); // Start transaction

    try {
      Gate::authorize("modify", $application);

      // Update the application status to 'rejected'
      $application->status = 'rejected';
      $application->save();

      DB::commit(); // Commit transaction

      return response()->json(['message' => 'Application rejected successfully.', 'data' => new ApplicationResource($application)]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }
}
