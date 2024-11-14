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
      return UserResource::collection($users);
    } catch (Exception $e) {
      // Handle exception and return error message
      return response()->json(['message' => 'Failed to retrieve users', 'error' => $e->getMessage()], 500);
    }
  }
  // Get number of users
  public function count()
  {
    try {

      $users_count = User::count();
      return response()->json([
        "message" => "User count retrieved successfully.",
        "data" => [
          "count" => $users_count
        ]
      ]);
    } catch (Exception $e) {
      // Handle exception and return error message
      return response()->json(['message' => 'Failed to retrieve user count', 'error' => $e->getMessage()], 500);
    }
  }
  // Store a new admin
  public function store(RegisterUserRequest $request)
  {
    DB::beginTransaction(); // Start transaction
    try {
      // Validate the request data
      $validated = $request->validated();
      // Create the user in the database
      // Check if email is unique (could be handled in validation rules)
      $user = User::create([
        'email' => $validated["email"],
        'password' => Hash::make($validated["password"]), // Hash the password securely
        'user_type' => "admin",
      ]);
      // If user creation is successful, create the user profile
      if ($user) {
        $user->userprofile()->create([
          'user_id' => $user->id,
          'first_name' => $validated["first_name"],
          'last_name' => $validated["last_name"],
          "bio" => $validated["bio"],
          "location" => $validated["location"],
          "phone_number" => $validated["phone_number"],
          "website" => $validated["website"]
        ]);

        // Create an API token (if using Sanctum or JWT)
        $token = $user->createToken('API Token')->plainTextToken;
        $user->refresh();
        DB::commit(); // Commit transaction if everything is successful
        // Return a success response with the token and user details
        return response()->json([
          "message" => "Admin created successfully",
          "user" => new UserResource($user),
          "token" => $token
        ], 201); // Return 201 Created for a successful resource creation
      }
      throw new Exception('User registration failed');
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => 'User registration failed', 'error' => $e->getMessage()], 500);
    }
  }
  // Show a specific user's details
  public function show(User $user)
  {
    try {
      // // Find the user by ID and load their profile
      // $user = User::with('userprofile')->findOrFail($id); // Use findOrFail to automatically throw a 404 error if user not found
      // // Return the user details as a resource
      return new UserResource($user);
    } catch (Exception $e) {
      // Return a 404 error if the user is not found
      return response()->json(['message' => 'User not found', 'error' => $e->getMessage()], 404);
    }
  }
  // Update user information
  public function update(UpdateUserRequest $request, User $user)
  {
    DB::beginTransaction(); // Start transaction
    try {
      // Validate the request data
      $validated = $request->validated();
      // Find the user profile associated with the user
      $profile = User_profile::where("user_id", $user->id)->first();

      // Update user details
      $user->update([
        'email' => $validated["email"],
        'password' => $user->password // Keep the old password if not changing (NB:allow updating password separately)
      ]);
      // $validated["first_name"] = $validated["first_name"];
      // $validated["last_name"] = $validated["last_name"];
      // Update user profile details
      $profile->update($validated);
      DB::commit(); // Commit transaction
      // Return success response
      return response()->json([
        'message' => 'User updated successfully',
        'user' => new UserResource($user),
      ], 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json(['message' => 'Failed to update user', 'error' => $e->getMessage()], 500);
    }
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
        return response()->json(['message' => 'User is already banned'], 200);
      }
      // Set the user user_status to 'banned'
      $user->user_status = 'banned';
      $user->save();
      $user->refresh();
      return response()->json([
        'message' => 'User has been banned successfully.',
        'data' => new UserResource($user)
      ], 200);
    } catch (Exception $e) {
      // Return an error message in case of failure
      return response()->json(['message' => 'Failed to bann user', 'error' => $e->getMessage()], 500);
    }
  }
  // Unban User
  public function unbanUser(User $user)
  {
    try {
      // Check if user is already active to avoid unnecessary updates
      if ($user->user_status === 'active') {
        return response()->json(['message' => 'User is already active'], 200);
      }
      // Set the user user_status to 'active'
      $user->user_status = 'active';
      $user->save();
      $user->refresh();

      return response()->json([
        'message' => 'User has been unbanned successfully.',
        'data' => new UserResource($user)
      ], 200);
    } catch (Exception $e) {
      // Return an error message in case of failure
      return response()->json(['message' => 'Failed to unbann user', 'error' => $e->getMessage()], 500);
    }
  }

  // Approve User
  public function approve(User $user)
  {
    try {
      // Check if user is already approved to avoid unnecessary updates
      if ($user->status === 'approved') {
        return response()->json(['message' => 'User is already approved'], 200);
      }
      // Set the user status to 'approved'
      $user->status = 'approved';
      $user->save();
      $user->refresh();

      return response()->json([
        'message' => 'User has been approved successfully.',
        'data' => new UserResource($user)
      ], 200);
    } catch (Exception $e) {
      // Return an error message in case of failure
      return response()->json(['message' => 'Failed to approve user', 'error' => $e->getMessage()], 500);
    }
  }
  // Reject User
  public function reject(User $user)
  {
    try {
      // Check if user is already rejected to avoid unnecessary updates
      if ($user->status === 'rejected') {
        return response()->json(['message' => 'User is already rejected'], 200);
      }
      // Set the user status to 'rejected'
      $user->status = 'rejected';
      $user->save();
      $user->refresh();
      return response()->json([
        'message' => 'User has been rejected.',
        'data' => new UserResource($user)
      ], 200);
    } catch (Exception $e) {
      // Return an error message in case of failure
      return response()->json(['message' => 'Failed to reject user', 'error' => $e->getMessage()], 500);
    }
  }
}
