<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\User_profile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IndividualController extends Controller
{
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
        'user_type' => "individual",
      ]);

      // Create the user profile if user creation is successful
      if ($user) {
        $user->userprofile()->create([
          'user_id' => $user->id,
          'first_name' => $validated["first_name"],
          'last_name' => $validated["last_name"],
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
          'message' => 'Employer(individual) registered successfully',
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
  // Show individual profile
  public function show(User $user)
  {
    Gate::authorize("modify", $user->userprofile);

    return response()->json([
      "status" => "success",
      "message" => "Profile retrieved successfully",
      "data" => new UserResource($user)
    ], 200); // 200 OK

  }

  public function update(RegisterUserRequest $request, User $user)
  {
    DB::beginTransaction(); // Start transaction
    Gate::authorize("modify", $user->userprofile);
    try {

      // Validate the request data
      $validated = $request->validated();
      // Check if the email is being updated and if it already exists
      if ($validated["email"] !== $user->email) {
        $existingUser = User::where('email', $validated["email"])->first();
        if ($existingUser) {
          return response()->json([
            'status' => 'error',
            'code' => 400,
            'error' => [
              'code' => 'EMAIL_ALREADY_EXISTS',
              'message' => 'The email address is already in use.',
            ]
          ], 400);
        }
      }

      // update user
      $user->update([
        'email' => $validated["email"],
      ]);
      // Create the user profile if user creation is successful
      $user->userprofile()->update([
        'user_id' => $user->id,
        'first_name' => $validated["first_name"],
        'last_name' => $validated["last_name"],
        'city' => $validated["city"],
        'state' => $validated["state"],
        "phone_number" => $validated["phone_number"],
        "linkedin_url" => $validated["linkedin_url"],
        "github_url" => $validated["github_url"],
        "twitter_url" => $validated["twitter_url"],
        "instagram_url" => $validated["instagram_url"],
      ]);

      // Refresh the user to ensure we retrieve the latest values
      $user->refresh();
      $user->load("userprofile");
      DB::commit(); // Commit transaction
      // Return the user data and token
      return response()->json([
        'status' => 'success',
        'message' => 'Profile Updated successfully',
        "data" => new UserResource($user),
      ], 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error

      Log::channel("api")->error("Error updating Profile", ['error' => $e->getMessage()]);
      return response()->json([
        'status' => 'error',
        'code' => 500,
        'error' => [
          'code' => 'SERVER_ERROR',
          'message' => 'Profile update failed',
        ]
      ], 500);
    }
  }

  public function uploadPhoto(Request $request, User $user)
  {
    Gate::authorize('modify', $user->userprofile);

    // Validate the uploaded profile_picture
    $request->validate([
      'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Delete the existing profile_picture file if it exists
    if ($request->has("profile_picture")) {
      // Store file in public folder
      $imagePath = $request->file("profile_picture")->store("profile_picture", "public");

      $validated = $imagePath;

      // Deleting previous image to store new one 
      Storage::disk("public")->delete($user->userprofile->profile_picture ?? "");

      $user->userprofile->profile_picture = $validated;
      $user->userprofile()->update(["profile_picture" => $validated]);
      $user->refresh();
    }
    return response()->json([
      'status' => 'success',
      'message' => "Profile picture uploaded successfully",
      'data' => new UserResource($user)
    ]);
  }
}
