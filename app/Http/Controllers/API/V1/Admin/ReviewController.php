<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
  // Fetch paginated list of reviews
  public function index()
  {
    try {
      // Retrieve latest reviews and paginate the results
      $reviews = Review::latest()->paginate(10); // Using latest() to order by creation date

      // Return reviews in a paginated format using ReviewResource
      return ReviewResource::collection($reviews);
    } catch (Exception $e) {
      // Return error message if the retrieval fails
      return response()->json(['message' => 'Failed to retrieve reviews', 'error' => $e->getMessage()], 500);
    }
  }

  // Get the totla no of reviews
  public function count()
  {
    try {
      $review_count = Review::count();

      return response()->json([
        "message" => "Review count retrieved successfully.",
        "data" => [
          "count" => $review_count
        ]
      ]);
    } catch (Exception $e) {
      // Return error message if the retrieval fails
      return response()->json(['message' => 'Failed to retrieve review count', 'error' => $e->getMessage()], 500);
    }
  }

  // Show a specific review
  public function show(Review $review)
  {
    // Authorize the user to view the review using the "view" policy
    Gate::authorize("update", $review);

    try {
      // Return the review details
      return new ReviewResource($review);
    } catch (Exception $e) {
      // Return error message if the retrieval fails
      return response()->json(['message' => 'Failed to retrieve review', 'error' => $e->getMessage()], 500);
    }
  }

  // Create a new review
  public function store(StoreReviewRequest $request)
  {
    DB::beginTransaction(); // Begin DB transaction for atomic operation

    try {
      // Validate incoming request
      $validated = $request->validated();

      // Assign the reviewer (current authenticated user) and reviewee
      $validated['reviewer_id'] = Auth::id(); // Get the current user’s ID
      // Create the review and save it to the database
      $review = Review::create($validated);

      DB::commit(); // Commit transaction if successful
      $review->refresh();
      // Return the created review data
      return ['message' => 'Review created successsfully', "data" => new ReviewResource($review)];
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction if there's an error
      return response()->json(['message' => 'Failed to create review', 'error' => $e->getMessage()], 500);
    }
  }

  // Update an existing review
  public function update(StoreReviewRequest $request, Review $review)
  {
    // Authorize the user to update the review using the "update" policy
    Gate::authorize("update", $review);

    DB::beginTransaction(); // Begin DB transaction for atomic operation

    try {
      // Validate incoming request
      $validated = $request->validated();

      // Update the review with the new validated data
      $review->update($validated);

      DB::commit(); // Commit transaction if successful

      // Return the updated review data
      return ['message' => 'Review updated successsfully', "data" => new ReviewResource($review)];
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction if there's an error
      return response()->json(['message' => 'Failed to update review', 'error' => $e->getMessage()], 500);
    }
  }

  // Delete a review
  public function destroy(Review $review)
  {
    // Authorize the user to delete the review using the "delete" policy
    Gate::authorize('delete', $review);

    DB::beginTransaction(); // Begin DB transaction for atomic operation

    try {
      // Delete the review from the database
      $review->delete();

      DB::commit(); // Commit transaction if successful

      // Return success message
      return response()->json(["message" => "Review deleted successfully"]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction if there's an error
      return response()->json(['message' => 'Failed to delete review', 'error' => $e->getMessage()], 500);
    }
  }

  // Approve a review
  public function approve(Review $review)
  {
    try {
      // Set the review status to 'approved'
      $review->status = 'approved';
      $review->save(); // Save the change
      $review->refresh();
      // Return success message
      return response()->json(["message" => "Review has been approved successfully", "data" => new ReviewResource($review)]);
    } catch (Exception $e) {
      // Return error message if the approval fails
      return response()->json(['message' => 'Failed to approve review', 'error' => $e->getMessage()], 500);
    }
  }

  // Reject a review
  public function reject(Review $review)
  {
    try {
      // Set the review status to 'rejected' (fix error in original code where status was set to 'approved' instead of 'rejected')
      $review->status = 'rejected';
      $review->save(); // Save the change

      $review->refresh();
      // Return success message
      return response()->json(["message" => "Review has been rejected successfully", "data" => new ReviewResource($review)]);
    } catch (Exception $e) {
      // Return error message if the rejection fails
      return response()->json(['message' => 'Failed to reject review', 'error' => $e->getMessage()], 500);
    }
  }
}
