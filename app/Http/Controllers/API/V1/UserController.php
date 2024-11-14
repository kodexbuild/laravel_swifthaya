<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserBankDetailsRequest;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Models\Bank;
use App\Models\Recipient;
use App\Models\User;
use App\Models\User_profile;
use App\Models\UserBankDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
  public function show()
  {
    $user = Auth::user();

    Gate::authorize("view", Auth::user()->userprofile);
    try {
      return response()->json([
        "data" => new UserResource($user),
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        "message" => "Failed to retrieve User",
        "error" => $e->getMessage()
      ], 500);
    }
  }

  public function update(UpdateUserRequest $request)
  {
    DB::beginTransaction(); // Start transaction

    try {
      $user = User::findorFail(Auth::user()->id);

      // Validate the request data
      $validated = $request->validated();
      // checking if email already exists
      $existingUser = User::where('email', $validated["email"])->first();

      if ($existingUser && $existingUser->id !== $user->id) {
        return response()->json([
          'message' => 'Failed to update user',
          'error' => 'Email is already taken by another user.'
        ], 409);
      }

      // Create the user
      $user->update([
        'email' => $validated["email"],
        'password' => $user->password,
      ]);

      // Create the user profile if user creation is successful
      if ($user) {
        $user->userprofile->update([
          'first_name' => $validated["first_name"] ?? $user->userprofile->first_name,
          'last_name' => $validated["last_name"] ?? $user->userprofile->last_name,
          "bio" => $validated["bio"] ?? $user->userprofile->bio,
          "location" => $validated["location"] ?? $user->userprofile->location,
          "phone_number" => $validated["phone_number"] ?? $user->userprofile->phone_number,
          "website" => $validated["website"] ?? $user->userprofile->website
        ]);


        // Refresh the user to ensure we retrieve the latest values
        $user->refresh();
        $user->load("userprofile");
        DB::commit(); // Commit transaction

        // Return the user data and token
        return response()->json([
          "data" => new UserResource($user),
        ], 201);
      }

      throw new Exception('User registration failed');
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      return response()->json([
        'message' => 'Failed to update user',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function updatePassword(Request $request)
  {
    $userId = Auth::user()->id;
    $user = User::findOrFail($userId);

    DB::beginTransaction(); // Start transaction

    try {
      // Validate password and current password
      $validated = $request->validate([
        'current_password' => 'required',
        'new_password' => [
          'required',
          Rules\Password::defaults()
        ],
      ]);

      // Check if current password is correct
      if (!Hash::check($validated['current_password'], $user->password)) {
        return response()->json([
          'message' => 'Current password is incorrect.'
        ], 403);  // Forbidden
      }

      // Update password
      $user->update([
        'password' => Hash::make($validated['new_password']),
      ]);

      DB::commit();  // Commit transaction if successful

      return response()->json([
        'message' => 'Password updated successfully.'
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();  // Rollback on error
      Log::error("Password update failed: " . $e->getMessage());

      return response()->json([
        'message' => 'Failed to update password.'
      ], 500);
    }
  }


  // store user account detail
  public function saveAccountDetails(UserBankDetailsRequest $request)
  {
    DB::beginTransaction();  // Start transaction

    try {
      $user = Auth::user();
      $validated = $request->validated();
      $validated["user_id"] = $user->id;

      $bank = Bank::where('name', $validated['bank_name'])->first();
      if (!$bank) {
        throw new \Exception('Bank not found');
      }

      // Verify account number
      $accountDetails = $this->verifyAccountNumber($validated['account_number'], $bank->code);
      if (!$accountDetails) {
        throw new \Exception('Account verification failed. Cannot resolve account.');
      }

      // Save verified details
      $userBankDetail = UserBankDetail::updateOrCreate(
        ['user_id' => $user->id],
        [
          'account_number' => $accountDetails['account_number'],
          'bank_code' => $accountDetails['bank_code'],
          'bank_name' => $validated['bank_name'],
          'account_name' => $accountDetails['account_name'],
        ]
      );

      // Create transfer recipient
      $createRecipient = $this->createTransferRecipient($user->id);
      if (!$createRecipient) {
        throw new \Exception('Failed to create transfer recipient');
      }

      DB::commit();  // Commit if everything is successful

      return response()->json(['status' => true, 'message' => 'Account details saved successfully.'], 200);
    } catch (\Exception $e) {
      DB::rollBack();  // Rollback on failure
      Log::error("Account details save failed: " . $e->getMessage());

      return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
    }
  }


  public function createTransferRecipient($userId)
  {
    $userBankDetails = UserBankDetail::where('user_id', $userId)->first();

    if (!$userBankDetails) {
      throw new \Exception('Bank details not found for this user');
    }

    $SECRET_KEY = env('PAYSTACK_SECRET_KEY');
    $url = "https://api.paystack.co/transferrecipient";

    $fields = [
      'type' => 'nuban',
      'name' => $userBankDetails->account_name,
      'account_number' => $userBankDetails->account_number,
      'bank_code' => $userBankDetails->bank_code,
      'currency' => 'NGN'
    ];

    $fieldsString = http_build_query($fields);

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer $SECRET_KEY",
      "Cache-Control: no-cache",
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute and process response
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if ($response && $response['status']) {
      // Save the recipient
      Recipient::create([
        'user_bank_details_id' => $userBankDetails->id,
        'code' => $response['data']['recipient_code'],
      ]);
      return true;
    } else {
      throw new \Exception($response['message'] ?? 'Failed to create transfer recipient');
    }
  }

  // verify account
  public function verifyAccountNumber($account_number, $bank_code)
  {
    $SECRET_KEY = env('PAYSTACK_SECRET_KEY');
    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => "https://api.paystack.co/bank/resolve?account_number=$account_number&bank_code=$bank_code",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $SECRET_KEY",
        "Cache-Control: no-cache",
      ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      Log::error("Account verification cURL Error: $err");
      return null;
    }

    $responseData = json_decode($response, true);

    if (isset($responseData['status']) && $responseData['status']) {
      return [
        'account_number' => $responseData['data']['account_number'],
        'bank_code' => $bank_code,
        'account_name' => $responseData['data']['account_name']
      ];
    } else {
      Log::warning("Account verification failed: " . ($responseData['message'] ?? 'Unknown error'));
      return null;
    }
  }


  public function profile_img(Request $request)
  {
    try {
      DB::beginTransaction();
      // fetch userprofile
      $profile = User_profile::where("id", Auth::user()->userprofile->id)->first();

      // user authorization
      Gate::authorize("update", $profile);

      try {   // validate image request
        $validated = $request->validate(['profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048']);
      } catch (ValidationException $e) {
        // Collect the error messages
        $errorMessages = $e->validator->errors()->all();
        $errorMessage = implode(', ', $errorMessages);

        return response()->json([
          "message" => "Failed to update Profile picture",
          "error" => $errorMessages

        ], 400);
      }



      if ($request->has("profile_picture")) {
        // stor file in public folder
        $imagePath = $request->file("profile_picture")->store("profile", "public");

        $validated = $imagePath;

        // deleting previous image to store new one 
        Storage::disk("public")->delete($profile->profile_picture ?? "");

        $profile->profile_picture = $validated;
        $profile->update(["profile_picture" => $validated]);
        DB::commit();
        return response()->json(["message" => "Profile picture uploaded successfully"], 200);
      }
      return response()->json(["error" => ["profile_picture" => "No Profile picture selected"]], 400);
    } catch (Exception $e) {
      DB::rollBack();
      return response()->json([
        "message" => "Failed to update Profile picture",
        "error" => $e->getMessage()
      ], 500);
    }
  }
  public function destroy(Request $request)
  {
    try {
      DB::beginTransaction();
      // Check if the authenticated user has permission to delete their own account
      Gate::authorize("delete", Auth::user()->userprofile);

      // Validate the request, ensuring the user provides their current password
      $request->validate([
        'password' => ['required', 'current_password'], // Laravel's 'current_password' rule checks if the password matches
      ]);


      // Get the authenticated user
      $user = $request->user();

      // Perform the deletion
      $user->delete();

      // Revoke the user's tokens to log them out from all devices
      $user->tokens()->delete();

      // Return a JSON response indicating the account deletion was successful
      DB::commit(); // Commit transaction

      return response()->json(["message" => "User account deleted successfully"]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to delete user account', 'error' => $e->getMessage()], 500);
    }
  }
}
