<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSwifthayajobRequest;
use App\Http\Requests\UpdateSwifthayajobRequest;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\SwifthayajobResource;
use App\Models\Application;
use App\Models\Company_profile;
use App\Models\Swifthayajob;
use App\Models\Talent_profile;
use App\Models\User;
use App\Models\User_profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class SwifthayajobController extends Controller
{
  // Retrieve jobs by company ID
  public function index()
  {
    $user = Auth::user(); // Fetch the authenticated user
    $jobs = Swifthayajob::where("company_id", $user->id)->latest()->paginate(10); // Filter jobs by company ID

    // if ($jobs->isEmpty()) { 
    //   return response()->json(["message" => "Company has no jobs"]);
    // }

    foreach ($jobs as $job) {
      Gate::authorize("view", $job); // Check if the user is authorized to view the job
    }

    return response()->json([
      "status" => "success",
      "message" => "Jobs retrieved successfully",
      "data" => SwifthayajobResource::collection($jobs),
    ]);
  }

  // Show specific job
  public function show(Swifthayajob $job)
  {
    Gate::authorize("view", $job); // Check if the user is authorized to view the job
    return response()->json([
      "status" => "success",
      "message" => "Job retrieved successfully",
      "data" => new SwifthayajobResource($job),
    ]);
  }

  // Create a new job
  public function store(StoreSwifthayajobRequest $request)
  {
    DB::beginTransaction(); // Begin DB transaction
    try {
      $user = Auth::user();
      $company = Company_profile::where("user_profile_id", $user->userprofile->id)->exists();

      if (!$company) {
        return response()->json(["status" => "error", "message" => "Can't create a job without company profile"], 400);
      }

      $validated = $request->validated(); // Validate the request data


      $validated["company_id"] = $user->id; // Associate the job with the authenticated user

      $job = $user->swifthayajob()->create($validated); // Create job

      $job->refresh(); // Reload the model to get default values 

      DB::commit(); // Commit transaction
      return response()->json([
        "status" => "success",
        "message" => "Job created successfully",
        "data" => new SwifthayajobResource($job)
      ], 201);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to create job',
      ], 500);
    }
  }

  // Update job

  public function update(StoreSwifthayajobRequest $request, Swifthayajob $job)
  {
    Gate::authorize("update", $job); // Ensure user is authorized to update the job
    $validated = $request->validated(); // Validate request data


    $job->update($validated); // Update job with new data

    DB::commit(); // Commit transaction
    return response()->json([
      "status" => "success",
      "message" => "Job updated successfully",
      "data" => new SwifthayajobResource($job)
    ], 201);
  }

  // Delete job
  public function destroy(Swifthayajob $job)
  {
    DB::beginTransaction(); // Begin DB transaction

    Gate::authorize("delete", $job); // Ensure user is authorized to delete the job
    $job->delete(); // Delete the job
    DB::commit(); // Commit transaction
    return response()->json(["message" => "Job deleted successfully"], 200);
  }

  // Offer job functionality (implementation missing)
  public function offer_job()
  {
    // TODO: Implement logic to return job offers from the database
    // Example: offers where employer_id = 1
  }

  // Search jobs with filters
  public function job_search(Request $request)
  {
    try {
      // $query = Swifthayajob::with("user")->where('status', "approved"); // Filter only approved jobs
      $query = Swifthayajob::with("user");


      // Filtering by title
      if ($request->filled('experience_level')) {
        $query->where('experience_level', 'like', '%' . $request->experience_level . '%');
      }

      // Filtering by salary_period
      if ($request->filled('salary_period')) {
        $query->where('salary_period', $request->salary_period);
      }

      // Filtering by location
      if ($request->filled('location')) {
        $query->where('location', 'like', '%' . $request->location . '%');
      }

      // Filtering by job_type
      if ($request->filled('job_type')) {
        $query->where('job_type', 'like', '%' . $request->job_type . '%');
      }

      $jobs = $query->latest()->paginate(10); // Paginate results (limit to 10 per page)
      return SwifthayajobResource::collection($jobs);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to search for jobs', 'error' => $e->getMessage()], 500);
    }
  }
}
