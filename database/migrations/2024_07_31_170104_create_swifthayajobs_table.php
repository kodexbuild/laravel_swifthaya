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
      // $table->string('required_skills')->nullable();
      $table->string('location')->nullable();
      $table->decimal('salary_amount', 10, 2)->nullable();
      $table->enum('salary_period', ['daily', 'weekly', 'monthly', 'annually', 'per_project'])->nullable();

      $table->text('job_summary')->nullable();
      $table->text('responsibilities')->nullable();
      $table->text('requirements')->nullable();
      $table->text('qualifications')->nullable();
      $table->enum('experience_level', ['entry', 'junior', 'intermediate', 'senior', 'lead']);
      $table->enum('job_type', ['full_time', 'part_time', 'contract', 'internship']);
      $table->timestamp('posted_at')->useCurrent();
      // $table->timestamp('deadline_date')->nullable();
      $table->enum('job_status', ['draft', 'published'])->default('draft');
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
      $table->timestamps();
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
