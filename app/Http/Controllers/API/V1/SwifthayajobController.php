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
use Carbon\Carbon;
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

  // Publish job

  public function publish(Swifthayajob $job)
  {
    // Ensure only the job owner can publish
    Gate::authorize('update', $job);
    DB::beginTransaction(); // Begin DB transaction

    // Prevent publishing if already published
    if ($job->job_status === 'published') {
      return response()->json([
        'status' => 'error',
        'message' => 'This job is already published.'
      ], 400);
    }
    // Update job to "published"
    $job->update([
      'posted_at' => Carbon::now(),
      'job_status' => 'published'
    ]);
    DB::commit(); // Commit transaction

    return response()->json([
      'status' => 'success',
      'message' => 'Job has been published successfully.',
      'data' => new SwifthayajobResource($job)
    ]);
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
      // $query = Swifthayajob::with("user")->where(['status' => "approved", "job_status" => "published"]); // Filter only approved jobs
      $query = Swifthayajob::with("user")->where('job_status', 'published');
      if ($request->filled('keyword')) {
        $query->where(function ($q) use ($request) {
          $q->orWhere('title', 'like', '%' . $request->keyword . '%');
          // Search for company name
          $q->orWhereHas('user.userProfile.companyProfile', function ($companyQuery) use ($request) {
            $companyQuery->where('company_name', 'like', '%' . $request->keyword . '%');
          });
        });
      }

      // Filtering by experience
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
      // Filter by date posted
      if ($request->filled('date_posted')) {
        switch ($request->date_posted) {
          case 'today':
            $query->whereDate('created_at', Carbon::today());
            break;
          case 'yesterday':
            $query->whereDate('created_at', Carbon::yesterday());
            break;
          case 'last_7_days':
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
            break;
          case 'last_30_days':
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
            break;
          case 'last_90_days':
            $query->where('created_at', '>=', Carbon::now()->subDays(90));
            break;
          case 'last_6_months':
            $query->where('created_at', '>=', Carbon::now()->subMonths(6));
            break;
          case 'last_12_months':
            $query->where('created_at', '>=', Carbon::now()->subMonths(12));
            break;
        }
      }

      $jobs = $query->latest()->paginate(10); // Paginate results (limit to 10 per page)
      return SwifthayajobResource::collection($jobs);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to search for jobs', 'error' => $e->getMessage()], 500);
    }
  }
}
