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
    Schema::create('talent_profiles', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_profile_id')->constrained()->cascadeOnDelete();
      $table->json('skills');
      $table->json('experience');
      $table->json('education');
      $table->json('portfolio');
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('talent_profiles');
  }
};
