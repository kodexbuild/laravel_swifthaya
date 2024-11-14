<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\Project;
use App\Models\Swifthayajob;
use App\Models\User_profile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
  // List all applications
  // public function index()
  // {
  //   try {
  //     // Fetch and paginate applications
  //     $applications = Application::latest()->paginate(10);

  //     return ApplicationResource::collection($applications);
  //   } catch (Exception $e) {
  //     return response()->json(['message' => $e->getMessage()], 400);  // No DB transaction here, so no rollback needed
  //   }
  // }

  // Get the total no project applications
  public function project_count()
  {
    try {
      // Fetch and paginate project applications
      $application_count = Application::whereNotNull("project_id")->count();

      return response()->json([
        "message" => "Project application count retrieved successfully.",
        "data" => [
          "count" => $application_count
        ]
      ]);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to retrieve project application count', 'error' => $e->getMessage()], 500);
    }
  }
  // Get the total no job applications
  public function job_count()
  {
    try {
      // Fetch and paginate job applications
      $application_count = Application::whereNotNull("swifthayajob_id")->count();

      return response()->json([
        "message" => "Job application count retrieved successfully.",
        "data" => [
          "count" => $application_count
        ]
      ]);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to retrieve job application count', 'error' => $e->getMessage()], 500);
    }
  }
  // Get all job applications
  public function jobApplications()
  {
    try {
      // Fetch and paginate job applications
      $applications = Application::with(["user", "swifthayajob"])->whereNotNull("swifthayajob_id")->latest()->paginate(10);

      return ApplicationResource::collection($applications);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to retrieve job applications', 'error' => $e->getMessage()], 500);
    }
  }

  // Get all project applications
  public function projectApplications()
  {
    try {
      // Fetch and paginate project applications
      $applications = Application::with(["user", "project"])->whereNotNull("project_id")->latest()->paginate(10);

      return ApplicationResource::collection($applications);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to retrieve project applications', 'error' => $e->getMessage()], 500);
    }
  }

  // Delete an application
  public function destroy(Application $application)
  {
    DB::beginTransaction(); // Start transaction

    try {
      // Delete application
      $application->delete();

      DB::commit(); // Commit transaction

      return response()->json(['message' => 'Application deleted successfully.'], 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => 'Failed to delete application ', 'error' => $e->getMessage()], 400);
    }
  }
}
