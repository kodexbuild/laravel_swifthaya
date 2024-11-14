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
    Schema::create('swifthayajobs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
      $table->string('title');
      $table->text('description');
      $table->string('required_skills')->nullable();
      $table->string('location')->nullable();
      $table->integer('salary_min')->nullable();
      $table->integer('salary_max')->nullable();
      $table->enum('job_type', ['full-time', 'part-time', 'contract']);
      $table->timestamps();
      $table->timestamp('posted_at')->useCurrent();
      $table->timestamp('deadline_date')->nullable();
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('swifthayajobs');
  }
};
