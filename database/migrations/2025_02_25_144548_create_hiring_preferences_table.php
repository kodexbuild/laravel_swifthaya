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
    Schema::create('hiring_preferences', function (Blueprint $table) {
      $table->id();
      $table->foreignId('company_profile_id')->constrained()->cascadeOnDelete();
      $table->string('experience_level');
      $table->string('education_level');
      $table->string('required_skills');
      $table->string('location_flexibility');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('hiring_preferences');
  }
};
