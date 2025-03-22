<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\User_profile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  // Get paginated list of users
  public function index()
  {
    try {
      // Retrieve users with their associated user profile
      $users = User::with("userprofile")->paginate(10);
      // Return the users as a resource collection
      return UserResource::collection($users)
        ->response()
        ->setStatusCode(200);;
    } catch (Exception $e) {
      // Handle exception and return error message
      return response()->json([
        "status" => "error",
        "code" => 500,
        'message' => 'Failed to retrieve users',
      ], 500);
    }
  }
  // Get number of users
  public function count()
  {
    try {

      $users_count = User::count();
      return response()->json([
        "status" => "success",
        "message" => "User count retrieved successfully.",
        "data" => [
          "count" => $users_count
        ]
      ]);
    } catch (Exception $e) {
      // Handle exception and return error message
      return response()->json([
        "status" => "error",
        "code" => 500,
        'message' => 'Failed to retrieve user count',
      ], 500);
    }
  }

  // Show a specific user's details
  public function show(User $user)
  {
    return response()->json([
      "status" => "success",
      "message" => "User retrieved successfully.",
      "data" => new UserResource($user)
    ]);
  }


  // // Delete user
  // public function destroy(User $user)
  // {
  //   try {
  //     // Soft delete or permanently delete based on business logic (Consider soft delete if necessary)
  //     $user->delete();
  //     // Return a success message
  //     return response()->json(['message' => 'User deleted successfully.'], 200);
  //   } catch (Exception $e) {
  //     // Return an error message in case of failure
  //     return response()->json(['message' => 'Failed to delete user', 'error' => $e->getMessage()], 500);
  //   }
  // }

  // Ban User
  public function banUser(User $user)
  {
    try {
      // Check if user is already banned to avoid unnecessary updates
      if ($user->user_status === 'banned') {
        return response()->json([
          "status" => "error",
          'message' => 'User is already banned'
        ], 400);
      }
      // Set the user user_status to 'banned'
      $user->user_status = 'banned';
      $user->save();
      $user->refresh();
      return response()->json([
        'status' => 'success',
        'message' => 'User has been banned successfully.',
        'data' => new UserResource($user)
      ], 200);
    } catch (Exception $e) {
      // Return an error message in case of failure
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to bann user',
      ], 500);
    }
  }
  // Unban User
  public function unbanUser(User $user)
  {
    try {
      // Check if user is already active to avoid unnecessary updates
      if ($user->user_status === 'active') {
        return response()->json([
          "status" => "error",
          'message' => 'User is not banned'
        ], 400);
      }
      // Set the user user_status to 'active'
      $user->user_status = 'active';
      $user->save();
      $user->refresh();

      return response()->json([
        'status' => 'success',
        'message' => 'User has been unbanned successfully.',
        'data' => new UserResource($user)
      ], 200);
    } catch (Exception $e) {
      // Return an error message in case of failure
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to unbann user',
      ], 500);
    }
  }

  // Approve User
  public function approve(User $user)
  {
    try {
      // Check if user is already approved to avoid unnecessary updates
      if ($user->status === 'approved') {
        return response()->json([
          "status" => "error",
          'message' => 'User is already approved'
        ], 400);
      }
      // Set the user status to 'approved'
      $user->status = 'approved';
      $user->save();
      $user->refresh();

      return response()->json([
        'status' => 'success',
        'message' => 'User has been approved successfully.',
        'data' => new UserResource($user)
      ], 200);
    } catch (Exception $e) {
      // Return an error message in case of failure
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to approve user',
      ], 500);
    }
  }
  // Reject User
  public function reject(User $user)
  {
    try {
      // Check if user is already rejected to avoid unnecessary updates
      if ($user->status === 'rejected') {
        return response()->json([
          'status' => 'error',
          'message' => 'User is already rejected'
        ], 400);
      }
      // Set the user status to 'rejected'
      $user->status = 'rejected';
      $user->save();
      $user->refresh();
      return response()->json([
        'status' => 'success',
        'message' => 'User has been rejected successfully.',
        'data' => new UserResource($user)
      ], 200);
    } catch (Exception $e) {
      // Return an error message in case of failure
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'message' => 'Failed to reject user',
      ], 500);
    }
  }
}
