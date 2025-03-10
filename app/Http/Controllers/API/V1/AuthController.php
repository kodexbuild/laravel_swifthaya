<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\StoreCompany_profileRequest;
use App\Http\Requests\StoreTalent_profileRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Http\Resources\UserResource;
use App\Models\Talent_profile;
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
