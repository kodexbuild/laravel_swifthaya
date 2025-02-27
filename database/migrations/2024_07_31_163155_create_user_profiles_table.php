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
    Schema::create('user_profiles', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('first_name')->nullable();
      $table->string('last_name')->nullable();
      $table->string('profile_picture')->nullable();
      $table->text('bio')->nullable();
      $table->string('city')->nullable();
      $table->string('state')->nullable();
      $table->string('phone_number', 20)->nullable();  // 20 characters to accommodate country codes and separators
      $table->string('website')->nullable();
      $table->string('linkedin_url')->nullable();
      $table->string('github_url')->nullable();
      $table->string('twitter_url')->nullable();
      $table->string('instagram_url')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_profiles');
  }
};
