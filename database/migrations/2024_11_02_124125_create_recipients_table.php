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
    Schema::create('recipients', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_bank_details_id')->constrained()->cascadeOnDelete(); // Links to user bank details
      $table->string('code')->unique();  // Unique code returned by the payment provider
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('recipients');
  }
};
