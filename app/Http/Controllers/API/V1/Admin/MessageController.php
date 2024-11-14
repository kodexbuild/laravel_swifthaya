<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
  // Fetch a list of conversations with pagination
  public function conversations()
  {
    try {
      // Fetch conversations and paginate (latest first)
      $conversations = Conversation::with("messages")->latest()->paginate(10);

      // Return paginated conversations as a resource collection
      // return $conversations;
      return ConversationResource::collection($conversations);
    } catch (Exception $e) {
      // Return error response if exception occurs
      return response()->json(['message' => 'Failed to retrieve conversations', 'error' => $e->getMessage()], 500);
    }
  }

  // Get the total no of messages
  public function message_count()
  {
    try {
      $message_count = Message::count();

      return response()->json([
        "message" => "Message count retrieved successfully.",
        "data" => [
          "count" => $message_count
        ]
      ]);
    } catch (Exception $e) {
      // Return error response if exception occurs
      return response()->json(['message' => 'Failed to retrieve message count', 'error' => $e->getMessage()], 500);
    }
  }

  // Get the total no of conversations
  public function conversation_count()
  {
    try {
      $conversation_count = Conversation::count();

      return response()->json([
        "conversation" => "Conversation count retrieved successfully.",
        "data" => [
          "count" => $conversation_count
        ]
      ]);
    } catch (Exception $e) {
      // Return error response if exception occurs
      return response()->json(['conversation' => 'Failed to retrieve conversation count', 'error' => $e->getMessage()], 500);
    }
  }
  
  // Fetch a single conversation
  public function ShowConversation(Conversation $conversation)
  {
    try {

      return new ConversationResource($conversation);
    } catch (Exception $e) {
      // Return error response if exception occurs
      return response()->json(['conversation' => 'Failed to retrieve conversation', 'error' => $e->getMessage()], 500);
    }
  }
  // Fetch a single message
  public function ShowMessage(Message $message)
  {
    try {
      return new MessageResource($message);
    } catch (Exception $e) {
      // Return error response if exception occurs
      return response()->json(['message' => 'Failed to retrieve message', 'error' => $e->getMessage()], 500);
    }
  }


  // Helper to check if an employer has messaged
  private function employerHasMessaged($sender_id, $recipient_id)
  {
    return Message::where('sender_id', $sender_id)
      ->where('recipient_id', $recipient_id)
      ->exists();
  }

  public function store_conversation_and_message(User $recipient, StoreMessageRequest $request)
  {
    DB::beginTransaction(); // Begin transaction

    try {
      $user = Auth::user();

      // Check if conversation already exists
      $conversation = Conversation::with("messages")->where(function ($query) use ($user, $recipient) {
        $query->where('user_id', $user->id)
          ->where('recipient_id', $recipient->id);
      })->orWhere(function ($query) use ($user, $recipient) {
        $query->where('user_id', $recipient->id)
          ->where('recipient_id', $user->id);
      })->first();


      // If conversation does not exist, create conversation
      if (is_null($conversation)) {

        // Create a new conversation
        $conversation = Conversation::create([
          'recipient_id' => $recipient->id,
          'user_id' => $user->id,
        ]);
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
      $conversation->refresh();
      DB::commit(); // Commit transaction

      return response()->json([
        "message" => "Message sent successfully",
        "conversation" => new ConversationResource($conversation),
      ], 201);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback in case of failure
      return response()->json(['message' => 'Failed to create conversation or send message', 'error' => $e->getMessage()], 500);
    }
  }
  // Delete a specific conversation 
  public function destroyConversation(Conversation $conversation)
  {
    Gate::authorize("modify", $conversation);
    DB::beginTransaction(); // Start database transaction

    try {
      // Delete conversation
      $conversation->delete();

      // Commit transaction
      DB::commit();

      // Return success response
      return response()->json(['message' => 'Conversation deleted successfully'], 200);
    } catch (Exception $e) {
      // Rollback transaction on failure
      DB::rollBack();

      // Return error response
      return response()->json(['message' => 'Failed to delete conversation', 'error' => $e->getMessage()], 500);
    }
  }


  // Delete a specific message
  public function destroyMessage(Message $message)
  {
    Gate::authorize("modify", $message);

    DB::beginTransaction(); // Start database transaction

    try {
      // Delete message
      $message->delete();

      // Commit transaction
      DB::commit();

      // Return success response
      return response()->json(['message' => 'Message deleted successfully'], 200);
    } catch (Exception $e) {
      // Rollback transaction on failure
      DB::rollBack();

      // Return error response
      return response()->json(['message' => 'Failed to delete message', 'error' => $e->getMessage()], 500);
    }
  }
}
