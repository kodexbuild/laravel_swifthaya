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
      $table->string('job_title')->nullable();
      $table->text('professional_bio')->nullable();
      $table->json('tech_skills')->nullable();
      $table->json('soft_skills')->nullable();
      $table->enum('experience_level', ['entry', 'mid_senior', 'senior', 'executive'])->nullable();
      $table->string('portfolio_url')->nullable();
      $table->string('resume')->nullable();
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
