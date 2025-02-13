<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\User_profile;
use Exception;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

  /**
   * Register a new user.
   */
  public function register_individual(RegisterUserRequest $request)
  {
    DB::beginTransaction(); // Start transaction

    try {

      // Validate the request data
      $validated = $request->validated();
      // Create the user
      $user = User::create([
        'email' => $validated["email"],
        'password' => Hash::make($validated["password"]),
        'user_type' => "individual",
      ]);

      // Create the user profile if user creation is successful
      if ($user) {
        $user->userprofile()->create([
          'user_id' => $user->id,
          'first_name' => $validated["first_name"],
          'last_name' => $validated["last_name"],
          'street_address' => $validated["street_address"],
          'city' => $validated["city"],
          'state' => $validated["state"],
          "phone_number" => $validated["phone_number"],
        ]);

        // Create an API token for the user
        $token = $user->createToken('API Token')->plainTextToken;

        // Refresh the user to ensure we retrieve the latest values
        $user->refresh();
        $user->load("userprofile");
        DB::commit(); // Commit transaction

        // Return the user data and token
        return response()->json([
          'status' => 'success',
          'message' => 'User registration successful',
          "data" => new UserResource($user),
          "token" => $token
        ], 201);
      }

      throw new Exception('User registration failed');
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'error' => [
          'code' => 'SERVER_ERROR',
          'message' => 'User registration failed',
        ]
      ], 500);
    }
  }
  public function register_company(RegisterUserRequest $request)
  {
    DB::beginTransaction(); // Start transaction

    try {

      // Validate the request data
      $validated = $request->validated();
      // Create the user
      $user = User::create([
        'email' => $validated["email"],
        'password' => Hash::make($validated["password"]),
        'user_type' => 'company',
      ]);

      // Create the user profile if user creation is successful
      if ($user) {
        $user_profile = $user->userprofile()->create([
          'user_id' => $user->id,
          'street_address' => $validated["street_address"],
          'city' => $validated["city"],
          'state' => $validated["state"],
          "phone_number" => $validated["phone_number"],
        ]);


        // Create the company profile
        $company_profile = $user_profile->companyprofile()->create([
          'user_profile_id' => $user_profile->id,
          'company_name' => $validated["company_name"],
          'company_website' => $validated["company_website"],
          'industry' => $validated["industry"],
        ]);
      }

      // Create an API token for the user
      $token = $user->createToken('API Token')->plainTextToken;
      $company_profile->refresh(); // Reload to get the default values
      $company_profile->load("userprofile");


      DB::commit(); // Commit transaction

      return response()->json([
        "message" => "Company profile created successfully",
        "data" => new CompanyProfileResource($company_profile),
        "token" => $token
      ], 201); // 201 Created

      throw new Exception('User registration failed');
    } catch (Exception $e) {
      Log::channel('api')->error("debuggu", ["error" => $e->getMessage()]);
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'error' => [
          'code' => 'SERVER_ERROR',
          'message' => 'User registration failed',
        ]
      ], 500);
    }
  }
  
  public function register_talent(RegisterUserRequest $request)
  {
    DB::beginTransaction(); // Start transaction

    try {

      // Validate the request data
      $validated = $request->validated();
      // Create the user
      $user = User::create([
        'email' => $validated["email"],
        'password' => Hash::make($validated["password"]),
        'user_type' => 'talent',
      ]);

      // Create the user profile if user creation is successful
      if ($user) {
        $user->userprofile()->create([
          'user_id' => $user->id,
          'first_name' => $validated["first_name"],
          'last_name' => $validated["last_name"],
          'street_address' => $validated["street_address"],
          'city' => $validated["city"],
          'state' => $validated["state"],
          "phone_number" => $validated["phone_number"],
        ]);

        // Create an API token for the user
        $token = $user->createToken('API Token')->plainTextToken;

        // Refresh the user to ensure we retrieve the latest values
        $user->refresh();
        $user->load("userprofile");
        DB::commit(); // Commit transaction

        // Return the user data and token
        return response()->json([
          "status" => "success",
          "message" => "User registered successfully",
          "data" => new UserResource($user),
          "token" => $token
        ], 201);
      }
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'error' => [
          'code' => 'SERVER_ERROR',
          'message' => 'User registration failed',
        ]
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
        'status' => 'success',
        'message' => 'Login successful',
        "data" => new UserResource($user),
        'token' => $token,
      ], 200);
    } catch (ValidationException $e) {
      DB::rollBack(); // Rollback transaction on validation error

      return response()->json([
        'status' => 'error',
        'message' => 'The provided credentials are incorrect. ',
        'suggestion' => 'Please input the correct email and password',
      ], 401);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on any other errors
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'error' => [
          'code' => 'SERVER_ERROR',
          'message' => 'Login failed',
        ]
      ], 500);
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
        'status' => 'error',
        'code' => 500,
        'error' => [
          'code' => 'SERVER_ERROR',
          'message' => 'Logout failed',
        ]
      ], 500);
    }
  }
}
