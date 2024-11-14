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
    Schema::create('projects', function (Blueprint $table) {
      $table->id();
      $table->foreignId('poster_id')->constrained('users')->cascadeOnDelete();
      $table->string('title');
      $table->text('description');
      $table->string('required_skills')->nullable();
      $table->decimal('budget')->nullable();
      $table->integer('duration')->nullable(); // Project duration in hours
      $table->timestamp('posted_at')->useCurrent(); // Project posting date
      $table->timestamp('deadline_date')->nullable();
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('projects');
  }
};
