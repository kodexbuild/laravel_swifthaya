<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
  public function index()
  {
    try {
      $jobs = Project::paginate(10);

      return ProjectResource::collection($jobs);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to retrieve Projects', 'error' => $e->getMessage()], 500);
    }
  }
  // Get the total no of projects
  public function count()
  {
    try {
      $project_count = Project::count();

      return response()->json([
        "message" => "Project count retrieved successfully.",
        "data" => [
          "count" => $project_count
        ]
      ]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to retrieve Project count', 'error' => $e->getMessage()], 500);
    }
  }

  public function show(Project $project)
  {
    Gate::authorize("view", $project);
    try {
      return new ProjectResource($project);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to retrieve Project', 'error' => $e->getMessage()], 500);
    }
  }

  public function store(StoreProjectRequest $request)
  {
    DB::beginTransaction(); // Begin DB transaction

    try {
      $user = Auth::user();

      $skillsArray = explode(',', request()->required_skills);
      $validated = $request->validated();

      $validated["poster_id"] = Auth::user()->id;
      $skillsArray = explode(',', $validated["skills"]);
      $project = $user->project()->create($validated);

      DB::commit(); // Commit transaction

      return new ProjectResource($project);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['messsage' => 'Failed to create project', 'error' => $e->getMessage()], 500);
    }
  }

  public function update(UpdateProjectRequest $request, Project $project)
  {
    DB::beginTransaction(); // Begin DB transaction

    try {
      $validated = $request->validated();
      $project->update($validated);

      DB::commit(); // Commit transaction

      return new ProjectResource($project);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to update project', 'error' => $e->getMessage()], 500);
    }
  }


  public function destroy(Project $project)
  {
    $project->delete();
    return redirect()->route('admin.projects')->with('success', 'Project deleted successfully.');
  }


  public function approve(Project $project)
  {
    // Set the project status to 'approved'
    $project->status = 'approved';
    $project->save();
    $project->refresh();

    return response()->json(["message" => "Project has been approved successfully", "data" => new ProjectResource($project)]);
  }

  // Reject project
  public function reject(Project $project)
  {
    // Set the project status to 'rejected'
    $project->status = 'rejected';
    $project->save();
    $project->refresh();

    return response()->json(["message" => "Project has been rejected successfully", "data" => new ProjectResource($project)]);
  }
}
