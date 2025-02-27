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
    Schema::create('company_profiles', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_profile_id')->constrained()->cascadeOnDelete();
      $table->string('company_name');
      $table->string('industry');
      $table->string('company_size')->nullable();
      $table->string('company_website');
      $table->year('founded_year')->nullable();
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('company_profiles');
  }
};
