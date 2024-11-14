<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('transfers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // Links transfer to a user
      $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();  // Links to recipient details
      $table->string('transfer_reference')->unique();  // Unique reference to track the transfer
      $table->integer('amount');  // Amount in smallest currency unit (e.g., kobo)
      $table->string('currency', 3)->default('NGN');  // Currency code
      $table->enum('status', [
        'pending',
        'success',
        'reversed',
        'failed',
        'otp',
        'abandoned',
        'blocked',
        'rejected',
        'received'
      ])->default('pending');
      $table->string('transfer_code')->nullable();  // Optional code from payment provider
      $table->timestamp('completed_at')->nullable();  // Timestamp when transfer completes
      $table->timestamps();  // Standard timestamps for created and updated
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transfers');
  }
};
