<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Project;
use App\Models\Review;
use App\Models\Swifthayajob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
  /**
   *View a single review
   */
  public function show(Review $review)
  {
    try {

      return new ReviewResource($review);
    } catch (Exception $e) {
      // Handle any exceptions and return a proper response
      return response()->json(['message' => 'Failed to retrieve reviews', 'error' => $e->getMessage()], 500);
    }
  }
  /**
   * Get all reviews created by the authenticated user (Reviewer).
   */
  public function reviewer()
  {
    try {
      // Fetch reviews made by the authenticated user (reviewer)
      $user_id = Auth::id(); // Use Auth::id() instead of Auth::user()->id for better readability
      $reviews = Review::where("reviewer_id", $user_id)->latest()->paginate(10);

      return ReviewResource::collection($reviews);
    } catch (Exception $e) {
      // Handle any exceptions and return a proper response
      return response()->json(['message' => 'Failed to retrieve reviews', 'error' => $e->getMessage()], 500);
    }
  }

  /**
   * Get all reviews received by the authenticated user (Reviewee).
   */
  public function reviewee()
  {
    try {
      // Fetch reviews received by the authenticated user (reviewee)
      $user_id = Auth::id();
      $reviews = Review::where(["reviewee_id" => $user_id, "status" => "approved"])->latest()->paginate(10);

      return ReviewResource::collection($reviews);
    } catch (Exception $e) {
      // Handle exceptions
      return response()->json(['message' => 'Failed to retrieve reviews', 'error' => $e->getMessage()], 500);
    }
  }

  /**
   * Store a new review for a specific reviewee.
   */
  public function store(StoreReviewRequest $request)
  {
    DB::beginTransaction(); // Begin database transaction

    try {
      $validated = $request->validated();

      if (Auth::user()->user_type === "company" || Auth::user()->user_type === "individual") {
        $reviewee = User::findorFail($validated["reviewee_id"]);
        if ($reviewee->user_type === "company") {
          return response()->json(["error" => "Employer cannot review another employer"], 400);
        }
        // Find if any job posted by the company has an application by the talent
        $company_Id = Auth::user()->id;
        $talentId = $validated["reviewee_id"];
        // job
        $hasappliedjob = Swifthayajob::where('company_id', $company_Id)
          ->whereHas('application', function ($query) use ($talentId) {
            $query->where(['applicant_id' => $talentId, "status" => "accepted"]);
          })->exists();

        // project
        $hasappliedproject = Project::where('poster_id', $company_Id)
          ->whereHas('application', function ($query) use ($talentId) {
            $query->where(['applicant_id' => $talentId, "status" => "accepted"]);
          })->exists();
        if ($hasappliedjob === false && $hasappliedproject === false) {
          return response()->json(["error" => "Employer cannot review talent who has not applied"], 400);
        }
      } elseif (Auth::user()->user_type === "talent") {
        $reviewee = User::findorFail($validated["reviewee_id"]);
        if ($reviewee->user_type === "talent") {
          return response()->json(["error" => "Talent cannot review another talent"], 400);
        }
        $talentId = Auth::user()->id;
        $company_Id = $validated["reviewee_id"];
        // job
        $hasappliedjob = Swifthayajob::where('company_id', $company_Id)
          ->whereHas('applications', function ($query) use ($talentId) {
            $query->where(['applicant_id' => $talentId, "status" => "accepted"]); // Or 'user_id' for talents
          })->exists();

        // projects
        $hasappliedproject = Project::where('poster_id', $company_Id)
          ->whereHas('applications', function ($query) use ($talentId) {
            $query->where(['applicant_id' => $talentId, "status" => "accepted"]); // Or 'user_id' for talents
          })->exists();
        if ($hasappliedjob === false && $hasappliedproject === false) {
          return response()->json(["error" => "Talent cannot review employer who has accepted application"]);
        }
      }

      // Validate the incoming request data

      // Add the reviewer ID to the validated data
      $validated['reviewer_id'] = Auth::id(); // Auth::id() for readability

      // Create a new review with the validated data
      $review = Review::create($validated);

      // Refresh the review instance to get updated attributes
      $review->refresh();

      DB::commit(); // Commit the transaction

      // Return success response with the newly created review
      return response()->json([
        "message" => "Review created successfully",
        "data" => new ReviewResource($review)
      ], 201); // 201 status code for created resources
    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction in case of failure
      return response()->json(['message' => 'Failed to create review', 'error' => $e->getMessage()], 500);
    }
  }

  /**
   * Update an existing review.
   */
  public function update(StoreReviewRequest $request, Review $review)
  {
    // Authorize the action using Gates
    Gate::authorize('update', $review);

    DB::beginTransaction(); // Begin transaction

    try {
      // Validate the request
      $validated = $request->validated();

      // Update the review with the validated data
      $review->update($validated);

      DB::commit(); // Commit transaction
      // Return success response with updated review data
      return response()->json([
        "message" => "Review updated successfully",
        "data" => new ReviewResource($review)
      ], 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => 'Failed to update review', 'error' => $e->getMessage()], 500);
    }
  }

  /**
   * Delete a specific review.
   */
  public function destroy(Review $review)
  {
    // Authorize the action using Gates
    Gate::authorize('delete', $review);

    DB::beginTransaction(); // Begin transaction

    try {
      // Delete the review
      $review->delete();

      DB::commit(); // Commit the transaction

      // Return success response for the deletion
      return response()->json(["message" => "Review deleted successfully"]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => 'Failed to delete review', 'error' => $e->getMessage()], 500);
    }
  }
}
