<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\User_profile;
use Exception;

class AuthController extends Controller
{

  /**
   * Register a new user.
   */
  public function register(RegisterUserRequest $request)
  {
    DB::beginTransaction(); // Start transaction

    try {

      // Validate the request data
      $validated = $request->validated();
      // Create the user
      $user = User::create([
        'email' => $validated["email"],
        'password' => Hash::make($validated["password"]),
        'user_type' => $validated["user_type"],
      ]);

      // Create the user profile if user creation is successful
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

        // Create an API token for the user
        $token = $user->createToken('API Token')->plainTextToken;

        // Refresh the user to ensure we retrieve the latest values
        $user->refresh();
        $user->load("userprofile");
        DB::commit(); // Commit transaction

        // Return the user data and token
        return response()->json([
          "data" => new UserResource($user),
          "token" => $token
        ], 201);
      }

      throw new Exception('User registration failed');
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        'message' => 'User registration failed',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Log the user in.
   */
  public function login(LoginUserRequest $request)
  {
    DB::beginTransaction(); // Start transaction

    try {
      // Validate request data
      $validated = $request->validated();
      $user = User::with("userprofile")->where("email", $validated["email"])->first();

      // Check if email exists and password is correct
      if (!$user || !Hash::check($validated["password"], $user->password)) {
        return response()->json([
          'message' => 'The provided credentials are incorrect. Please input the correct email and password'
        ], 401);
      }

      // Create a new token for the user
      $token = $user->createToken('API Token')->plainTextToken;

      // Update last login timestamp
      $user->update(["last_login_at" => now()]);

      DB::commit(); // Commit transaction

      return response()->json([
        'message' => 'Login successful',
        "data" => new UserResource($user),
        'token' => $token,
      ], 200);
    } catch (ValidationException $e) {
      DB::rollBack(); // Rollback transaction on validation error
      return response()->json(['errors' => $e->errors()], 401);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on any other errors
      return response()->json(['message' => 'Login failed', 'error' => $e->getMessage()], 500);
    }
  }

  /**
   * Log the user out.
   */
  public function logout(Request $request)
  {
    try {
      // Revoke all tokens for the user
      $request->user()->tokens()->delete(); // This will revoke all tokens the user has

      return response()->json([
        'message' => 'Logout successful'
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        'message' => 'Logout failed',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}
