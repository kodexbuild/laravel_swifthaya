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
    Schema::create('refunds', function (Blueprint $table) {
      $table->id();
      $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
      $table->decimal('amount', 10, 2); // Amount refunded
      $table->enum('refund_type', ['full', 'partial']); // Full or partial refund
      $table->enum('status', ['initiated','pending', 'processing', "processed", 'failed'])->default("pending"); // Refund status
      $table->string('refund_reference')->nullable(); // Refund reference from provider
      $table->timestamps(); // For tracking timestamps        
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('refunds');
  }
};
