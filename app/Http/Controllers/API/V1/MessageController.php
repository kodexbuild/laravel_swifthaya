<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
  public function conversations()
  {
    try {
      // Retrieve conversations involving the authenticated user
      $conversations = Conversation::where('user_id', Auth::user()->id)
        ->orWhere('recipient_id', Auth::user()->id)
        ->latest()
        ->paginate(10);

      return ConversationResource::collection($conversations)
        ->response()
        ->setStatusCode(200);
    } catch (Exception $e) {
      return response()->json([
        "status" => "error",
        
        'message' => 'Failed to retrieve conversations',
      ], 500);
    }
  }

  public function messages()
  {
    try {
      // Retrieve messages involving the authenticated user
      $messages = Message::with("sender")->where('sender_id', Auth::user()->id)
        ->orWhere('recipient_id', Auth::user()->id)
        ->latest()
        ->paginate(10);

      return MessageResource::collection($messages);
    } catch (Exception $e) {
      return response()->json(['message' => 'Failed to retrieve messages', 'error' => $e->getMessage()], 500);
    }
  }

  public function store_conversation_and_message(User $recipient, StoreMessageRequest $request)
  {
    DB::beginTransaction(); // Begin transaction

    try {
      $user = Auth::user();

      // Check if conversation already exists
      $conversation = Conversation::where(function ($query) use ($user, $recipient) {
        $query->where('user_id', $user->id)
          ->where('recipient_id', $recipient->id);
      })->orWhere(function ($query) use ($user, $recipient) {
        $query->where('user_id', $recipient->id)
          ->where('recipient_id', $user->id);
      })->first();


      // If conversation does not exist, create conversation
      if (is_null($conversation)) {
        $this->validateConversationStart($user, $recipient);

        // Create a new conversation
        $conversation = Conversation::create([
          'recipient_id' => $recipient->id,
          'user_id' => $user->id,
        ]);
      }

      // Employer must send the first message
      if ($user->user_type === "talent") {
        if (!$this->employerHasMessaged($recipient->id, $user->id)) {
          return response()->json([
            "message" => "Can't send messages, till employer sends first message"
          ], 403);
        }
      }
      if ($user->id === $recipient->id) {
        return response()->json(["error" => "User cannot message themselves"], 400);
      }
      // Validate message content
      $validated = $request->validated();
      // Store the message in the conversation
      $message = $conversation->messages()->create([
        'recipient_id' => $recipient->id,
        'sender_id' => $user->id,
        'content' => $validated['content'],
        'status' => 'sent',
        'sent_at' => now(),
      ]);
      $message->refresh();

      DB::commit(); // Commit transaction
      // return $message;

      return response()->json([
        "message" => "Message sent successfully",
        "data" => new MessageResource($message)
      ], 201);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to send message', 'error' => $e->getMessage()], 500);
    }
  }

  // Helper to validate if a conversation can be started
  private function validateConversationStart($user, $recipient)
  {
    if ($user->user_type === "talent") {
      return response()->json(["error" => "Only employers can start conversations"], 403);
    }

    if ($recipient->user_type === "company" || $recipient->user_type === "individual") {
      return response()->json(["error" => "Can't create conversation with an employer"], 403);
    }

    $employerId = $user->id;

    $hasAppliedJob = Application::whereHas('swifthayajob', function ($query) use ($employerId) {
      $query->where('employer_id', $employerId);
    })->where('applicant_id', $recipient->id)->exists();

    $hasAppliedProject = Application::whereHas('project', function ($query) use ($employerId) {
      $query->where('poster_id', $employerId);
    })->where('applicant_id', $recipient->id)->exists();

    if (!$hasAppliedJob && !$hasAppliedProject) {
      return response()->json(["error" => "Can't create conversation with a candidate who hasn't applied", 403]);
    }
  }

  // Helper to check if an employer has messaged
  private function employerHasMessaged($sender_id, $recipient_id)
  {
    return Message::where('sender_id', $sender_id)
      ->where('recipient_id', $recipient_id)
      ->exists();
  }

  public function destroyConversation(Conversation $conversation)
  {
    Gate::authorize("modify", $conversation);

    DB::beginTransaction();
    try {
      $conversation->delete();
      DB::commit();
      return response()->json(['message' => 'Conversation deleted successfully']);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on failure
      return response()->json(['message' => 'Failed to delete conversation', 'error' => $e->getMessage()], 500);
    }
  }

  public function destroyMessage(Message $message)
  {
    Gate::authorize("modify", $message);

    DB::beginTransaction();
    try {
      $message->delete();
      DB::commit();
      return response()->json(['message' => 'Message deleted successfully']);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback on failure
      return response()->json(['message' => 'Failed to delete message', 'error' => $e->getMessage()], 500);
    }
  }
}
