<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;

class ProjectController extends Controller
{
  public function index()
  {
    try {
      $user = Auth::user();

      // Use Gate::forUser to authorize for each project
      $projects = Project::where("poster_id", $user->id)->latest()->paginate(10);
      // if ($projects->isEmpty()) {
      //   return response()->json(['message' => 'User has no projects'], 404);
      // }
      foreach ($projects as $project) {
        Gate::forUser($user)->authorize("view", $project);
      }

      return ProjectResource::collection($projects);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to retrieve projects', 'error' => $e->getMessage()], 500);
    }
  }


  public function show(Project $project)
  {
    try {
      Gate::authorize("view", $project);
      return new ProjectResource($project);
    } catch (Exception $e) {
      return response()->json(['messsage' => 'Failed to retrieve project', 'error' => $e->getMessage()], 500);
    }
  }

  public function store(StoreProjectRequest $request)
  {
    DB::beginTransaction();  // Begin transaction

    try {
      $user = Auth::user();

      $validated = $request->validated();
      $validated["poster_id"] = $user->id;  // Set the poster_id


      $project = $user->project()->create($validated);  // Ensure relationship is defined
      
      $project->refresh();  // Refresh to get the default values (e.g., status)

      DB::commit();  // Commit transaction

      return response()->json(["message" => "Project created successfully", "data" => new ProjectResource($project)], 201);
    } catch (Exception $e) {
      DB::rollBack();  // Rollback if error
      return response()->json(['message' => 'Failed to create project', 'error' => $e->getMessage()], 500);
    }
  }


  public function update(UpdateProjectRequest $request, Project $project)
  {
    DB::beginTransaction();  // Begin transaction

    try {
      Gate::authorize("update", $project);  // Check authorization

      $validated = $request->validated();
      
      $project->update($validated);  // Update project

      DB::commit();  // Commit transaction

      return [
        "message" => "Project updated successfully",
        "data" => new ProjectResource($project)
      ];
    } catch (Exception $e) {
      DB::rollBack();  // Rollback if error
      return response()->json(['message' => 'Failed to update project', 'error' => $e->getMessage()], 500);
    }
  }


  public function destroy(Project $project)
  {
    DB::beginTransaction();  // Begin transaction

    try {
      Gate::authorize("delete", $project);  // Check authorization

      $project->delete();  // Delete project

      DB::commit();  // Commit transaction

      return response()->json(["message" => "Project deleted successfully"]);
    } catch (Exception $e) {
      DB::rollBack();  // Rollback if error
      return response()->json(['message' => 'Failed to delete project', 'error' => $e->getMessage()], 500);
    }
  }


  public function project_search(Request $request)
  {
    try {
      // Default query with eager loading for the user relation
      $query = Project::with("user")->where('status', "approved");

      if (!empty($request->keyword)) {
        $query->where(function ($q) use ($request) {
          $q->orWhere('title', 'like', '%' . $request->keyword . '%')
            ->orWhere('required_skills', 'like', '%' . $request->keyword . '%')
            ->orWhere('duration', 'like', '%' . $request->keyword . '%')
            ->orWhere('budget', 'like', '%' . $request->keyword . '%');
        });
      }

      // Add additional filters
      if ($request->filled('title')) {
        $query->where('title', 'like', '%' . $request->title . '%');
      }
      if ($request->filled('required_skills')) {
        $query->where('required_skills', 'like', '%' . $request->required_skills . '%');
      }
      if ($request->filled('duration')) {
        $query->where('duration', 'like', '%' . $request->duration . '%');
      }
      if ($request->filled('budget')) {
        $query->where('budget', 'like', '%' . $request->budget . '%');
      }

      $projects = $query->latest()->paginate(10);

      return ProjectResource::collection($projects);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to search for projects', 'error' => $e->getMessage()], 500);
    }
  }
}
