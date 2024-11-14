<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Bank;
use App\Models\Payment;
use App\Models\Recipient;
use App\Models\Refund;
use App\Models\Transfer;
use App\Models\UserBankDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class PaymentController extends Controller
{
  // Initialize payment method
  public function init(Request $request)
  {
    DB::beginTransaction();
    try {
      $SECRET_KEY = env("PAYSTACK_SECRET_KEY");
      $url = "https://api.paystack.co/transaction/initialize";

      // Validate request
      $validated = $request->validate([
        "amount" => "required|numeric|min:1"
      ]);
      $amount = $validated["amount"] * 100;  // Convert to kobo

      $fields = [
        'email' => Auth::user()->email,
        'amount' => $amount
      ];

      $fields_string = http_build_query($fields);

      // Open cURL connection for transaction initialization
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $SECRET_KEY",
        "Cache-Control: no-cache",
      ]);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Execute cURL request and handle response
      $result = json_decode(curl_exec($ch), true);
      if ($result && $result['status']) {
        $naira_amount = $amount / 100;
        Payment::create([
          'user_id' => Auth::user()->id,
          'payment_reference' => $result['data']['reference'],
          'payer_type' => Auth::user()->user_type,
          'net_amount' => 0.00,
          'amount' => $naira_amount,
          'balance' => $naira_amount,
          'currency' => "NGN",
          'platform_fee' => 0.00,
          'payment_status' => 'pending',
          'payment_method' => 'Paystack',
        ]);

        DB::commit();

        // Return response to client
        return response()->json([
          'status' => true,
          'authorization_url' => $result['data']['authorization_url'],
          'reference' => $result['data']['reference'],
        ], 200);
      } else {
        return response()->json([
          'status' => false,
          'message' => $result["message"] ?? 'Payment initialization failed',
        ], 400);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Payment Initialization Error: ' . $e->getMessage());

      return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
  }

  // Fetch transaction history
  public function transactionHistory()
  {
    try {
      $payments = Payment::where('user_id', Auth::id())->latest()->paginate(10);

      return response()->json([
        'status' => true,
        'message' => 'Transaction history retrieved successfully',
        'data' => $payments,
      ], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Failed to retrieve payment history', 'error' => $e->getMessage()], 500);
    }
  }

  // Create transfer
  public function initiateTransfer(Request $request, Recipient $recipient)
  {
    DB::beginTransaction();
    try {
      $validated = $request->validate([
        'amount' => 'required|integer|min:1',
      ]);

      $user = Auth::user();
      $SECRET_KEY = env('PAYSTACK_SECRET_KEY');
      $transferReference = Str::uuid();

      $fields = [
        'source' => 'balance',
        'amount' => $validated['amount'] * 100,
        'recipient' => $recipient->id,
        'reference' => $transferReference,
      ];

      $fields_string = http_build_query($fields);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transfer");
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $SECRET_KEY",
        "Cache-Control: no-cache",
      ]);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $response = json_decode(curl_exec($ch), true);

      if ($response && $response['status']) {
        Transfer::create([
          'user_id' => $user->id,
          'recipient_code' => $validated['recipient_code'],
          'amount' => $validated['amount'] / 100,
          'transfer_reference' => $transferReference,
          'transfer_code' => $response['data']['transfer_code'],
        ]);

        DB::commit();

        return response()->json([
          'status' => true,
          'message' => 'Transfer initiated successfully',
          'data' => $response['data']
        ], 201);
      } else {
        DB::rollBack();
        return response()->json([
          'status' => false,
          'message' => 'Transfer initiation failed',
          'error' => $response['message'] ?? 'An error occurred'
        ], 400);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
  }

  // Refund payment with retry and exponential backoff
  public function refundPayment(Request $request)
  {
    DB::beginTransaction();

    try {
      $validated = $request->validate([
        "reference" => "required",
        "amount" => "nullable|integer|min:1"
      ]);

      $paymentReference = $validated["reference"];
      $payment = Payment::where('payment_reference', $paymentReference)->first();

      if (!$payment) {
        return response()->json(["error" => "Payment not found", "message" => "Invalid transaction reference"], 404);
      }

      if ($payment->payment_status !== 'completed') {
        return response()->json(["error" => "Payment cannot be refunded because it is not completed."], 400);
      }

      $amount = $request->has("amount") ? $validated["amount"] * 100 : $payment->net_amount * 100;

      if ($amount > ($payment->balance * 100)) {
        return response()->json(["error" => "Insufficient balance", "message" => "Refund amount exceeds balance"], 400);
      }

      // Exponential backoff for network issues
      $maxAttempts = 5;
      $attempt = 0;
      $delay = 1;

      while ($attempt < $maxAttempts) {
        $SECRET_KEY = env('PAYSTACK_SECRET_KEY');
        $url = "https://api.paystack.co/refund";
        $fields = ['transaction' => $paymentReference, 'amount' => $amount];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
          "Authorization: Bearer $SECRET_KEY",
          "Cache-Control: no-cache",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = json_decode(curl_exec($ch), true);
        $errorNumber = curl_errno($ch);
        curl_close($ch);

        if ($errorNumber === 0 && $response && isset($response['status']) && $response['status'] === true) {
          $naira_amount = $amount / 100;
          $refunded_amount = $naira_amount + $payment->refunded_amount;
          $balance = $payment->net_amount - $refunded_amount;
          $refund_type = ($naira_amount == intval($payment->net_amount)) ? "full" : "partial";

          $payment->update([
            "refunded_amount" => $refunded_amount,
            "balance" => $balance
          ]);

          Refund::create([
            "payment_id" => $payment->id,
            "amount" => $naira_amount,
            "refund_type" => $refund_type,
            "status" => 'initiated',
            "refund_reference" => $response["data"]["transaction"]["reference"]
          ]);

          DB::commit();

          return response()->json([
            'status' => true,
            'message' => 'Refund successful',
            'refund_reference' => $response["data"]["transaction"]["reference"],
            'refund_type' => $refund_type,
            'refunded_amount' => $refunded_amount,
            'balance' => $balance
          ], 200);
        } elseif ($errorNumber || isset($response['status']) && !$response['status']) {
          if ($errorNumber || strpos($response['message'], 'Too many requests') !== false) {
            Log::warning("Network or rate limit error: Retrying...");
            $attempt++;
            if ($attempt >= $maxAttempts) {
              Log::error("Max retries reached for refund");
              return response()->json(['status' => false, 'message' => "Network error or rate limit exceeded. Try again later."], 429);
            }
            sleep($delay);
            $delay *= 2;
          } else {
            throw new \Exception('Refund failed: ' . $response['message']);
          }
        } else {
          throw new \Exception('Refund failed due to unexpected error');
        }
      }
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Refund Error: ' . $e->getMessage());

      return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
  }



  // bank names and code
  public function listBanks()
  {
    $SECRET_KEY = env("PAYSTACK_SECRET_KEY");

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.paystack.co/bank",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $SECRET_KEY",
        "Cache-Control: no-cache",
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }
  }




  // Confirm/verify payment
  public function confirmPayment(Request $request)
  {
    DB::beginTransaction();  // Start transaction

    try {
      $validated = $request->validate(['reference' => 'required|string']);
      $reference = $validated["reference"];
      $SECRET_KEY = env('PAYSTACK_SECRET_KEY');

      // Verify payment with Paystack
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/$reference",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "Authorization: Bearer $SECRET_KEY",
          "Cache-Control: no-cache",
        ),
      ));

      $response = json_decode(curl_exec($curl), true);
      $err = curl_error($curl);
      curl_close($curl);

      // Check for cURL errors
      if ($err) {
        Log::error("Payment verification cURL Error", ["error" => $err]);
        throw new \Exception("Payment verification failed with cURL error: $err");
      }

      // Check for errors in the Paystack response
      if (!$response['status']) {
        throw new \Exception($response['message'] ?? 'Verification failed');
      }

      // Check if response data exists and payment was successful
      if (!isset($response['data']) || $response['data']['status'] !== 'success') {
        throw new \Exception($response["data"]['gateway_response'] ?? 'Payment verification failed');
      }

      // Retrieve and update the payment record if it exists
      $payment = Payment::where('payment_reference', $validated['reference'])->first();
      if (!$payment) {
        throw new \Exception("Payment record not found for reference: $reference");
      }

      // If already verified, return early
      if ($payment->payment_status === 'completed') {
        DB::rollBack();  // Rollback transaction, as no new update is needed
        return response()->json(['status' => true, 'message' => 'Payment has already been verified'], 200);
      }

      // Update payment record in the database
      $payment->update([
        'payment_status' => 'completed',
      ]);

      DB::commit();  // Commit the transaction

      return response()->json([
        'status' => true,
        'message' => 'Payment verified successfully',
        'data' => new PaymentResource($payment)
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();  // Rollback transaction if an exception is caught
      Log::error("Payment Confirmation Error: " . $e->getMessage());

      return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
  }




  // Webhook handler
  public function handleWebhook(Request $request)
  {
    DB::beginTransaction();  // Start the transaction

    try {
      Log::info('Webhook received:', $request->all());

      // Validate request signature
      if ((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') || !array_key_exists('HTTP_X_PAYSTACK_SIGNATURE', $_SERVER)) {
        throw new \Exception('Invalid request');
      }

      $input = @file_get_contents("php://input");
      $SECRET_KEY = env('PAYSTACK_SECRET_KEY');

      // Validate the signature
      if ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $SECRET_KEY)) {
        throw new \Exception('Invalid signature');
      }

      http_response_code(200);  // Acknowledge webhook immediately

      $event = json_decode($input);  // Parse the event payload

      // Handle payment success event
      if ($event->event === 'charge.success') {
        $reference = $event->data->reference;
        $payment = Payment::where('payment_reference', $reference)->first();

        if ($payment) {
          $grossAmount = $event->data->amount / 100;  // Convert to Naira
          $platformFee = $grossAmount * 0.1;  // Calculate platform fee
          $netAmount = $grossAmount - $platformFee;

          // Update payment record
          $payment->update([
            'net_amount' => $netAmount,
            'amount' => $grossAmount,
            'platform_fee' => $platformFee,
            'balance' => $netAmount,
            'currency' => $event->data->currency,
            'payment_status' => 'completed',
          ]);
        }
      }

      // Handle transfer status updates
      if ($event->event === 'transfer') {
        $reference = $event->data->reference;
        $status = $event->data->status;
        $transfer = Transfer::where('transfer_reference', $reference)->first();

        if ($transfer) {
          switch ($status) {
            case 'pending':
              $transfer->update(['status' => 'pending']);
              break;
            case 'success':
              $transfer->update(['status' => 'completed', 'completed_at' => now()]);
              break;
            case 'reversed':
              $transfer->update(['status' => 'reversed']);
              break;
            case 'failed':
              $transfer->update(['status' => 'failed']);
              break;
            case 'otp':
              $transfer->update(['status' => 'otp']);
              break;
            case 'abandoned':
              $transfer->update(['status' => 'abandoned']);
              break;
            case 'blocked':
              $transfer->update(['status' => 'blocked']);
              break;
            case 'rejected':
              $transfer->update(['status' => 'rejected']);
              break;
            case 'received':
              $transfer->update(['status' => 'received', 'completed_at' => now()]);
              break;
            default:
              Log::warning("Unhandled transfer status: $status");
              break;
          }
        }
      }

      // Handle refund status updates
      if (strpos($event->event, 'refund.') === 0) {
        $reference = $event->data->transaction_reference ?? null;
        $refundStatus = str_replace('refund.', '', $event->event);
        if ($reference) {
          Refund::where('refund_reference', $reference)->update(['status' => $refundStatus]);
        } else {
          Log::warning(" Refund status update failed due to missing reference.");
        }
      }

      DB::commit();  // Commit the transaction if successful
    } catch (\Exception $e) {
      DB::rollBack();  // Rollback the transaction if something fails
      Log::error('Webhook handling error: ' . $e->getMessage());

      return response()->json(['error' => $e->getMessage()], 400);
    }

    exit();  // Exit after processing
  }




  // Mark expired payments
  public function markExpiredPayments()
  {
    $expiredPayments = Payment::where('payment_status', 'pending')
      ->where('created_at', '<', now()->subHours(1))  // Expire after 1 hour
      ->get();

    foreach ($expiredPayments as $payment) {
      $payment->update([
        'payment_status' => 'expired',
      ]);
    }

    Log::info("Expired payments marked as expired: " . $expiredPayments->count());
  }
}
