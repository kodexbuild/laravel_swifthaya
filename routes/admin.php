<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProfilesController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SwifthayajobController;
use App\Http\Controllers\Admin\UserController;
use App\Models\Swifthayajob;
use Illuminate\Support\Facades\Route;




Route::prefix('admin')->middleware("auth", "can:admin", "verified")->group(function () {
  Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
  // Add other routes for managing users, jobs, etc.


  Route::get('/users', [UserController::class, 'index'])->name('admin.users');
  Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
  Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
  Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
  Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
  Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
  Route::get('/users/{user}/approve', [UserController::class, 'approveUser'])->name('admin.users.approve');
  Route::get('/users/{user}/reject', [UserController::class, 'rejectUser'])->name('admin.users.reject');


  // jobs
  Route::get('/jobs', [SwifthayajobController::class, 'index'])->name('admin.jobs');
  Route::get('/jobs/create', [SwifthayajobController::class, 'create'])->name('admin.jobs.create');
  Route::post('/jobs', [SwifthayajobController::class, 'store'])->name('admin.jobs.store');
  Route::get('/jobs/{job}/edit', [SwifthayajobController::class, 'edit'])->name('admin.jobs.edit');
  Route::put('/jobs/{job}', [SwifthayajobController::class, 'update'])->name('admin.jobs.update');
  Route::delete('/jobs/{job}', [SwifthayajobController::class, 'destroy'])->name('admin.jobs.destroy');
  Route::get('/jobs/{job}/approve', [SwifthayajobController::class, 'approveJob'])->name('admin.jobs.approve');
  Route::get('/jobs/{job}/reject', [SwifthayajobController::class, 'rejectJob'])->name('admin.jobs.reject');

  // projects

  Route::get('/projects', [ProjectController::class, 'index'])->name('admin.projects');
  Route::get('/projects/create', [ProjectController::class, 'create'])->name('admin.projects.create');
  Route::post('/projects', [ProjectController::class, 'store'])->name('admin.projects.store');
  Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('admin.projects.edit');
  Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('admin.projects.update');
  Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('admin.projects.destroy');
  Route::get('/projects/{project}/approve', [ProjectController::class, 'approveProject'])->name('admin.projects.approve');
  Route::get('/projects/{project}/reject', [ProjectController::class, 'rejectProject'])->name('admin.projects.reject');

  /* talent/company profile */
  // Talent Management
  Route::get('/talents', [ProfilesController::class, 'listTalents'])->name('admin.talents');
  Route::get('/talents/{user_profile}/create', [ProfilesController::class, 'createTalent'])->name('admin.talents.create');
  Route::post('/talents/{user_profile}create', [ProfilesController::class, 'storeTalent'])->name('admin.talents.store');

  Route::get('/talents/{talent}', [ProfilesController::class, 'viewTalent'])->name('admin.talents.view');
  Route::get('/talents/{talent}/edit', [ProfilesController::class, 'editTalent'])->name('admin.talents.edit');
  Route::patch('/talents/{talent}/edit', [ProfilesController::class, 'updateTalent'])->name('admin.talents.update');
  Route::delete('/talents/{talent}', [ProfilesController::class, 'deleteTalent'])->name('admin.talents.destroy');

  Route::get('/talents/{talent}/approve', [ProfilesController::class, 'approveTalent'])->name('admin.talents.approve');
  Route::get('/talents/{talent}/reject', [ProfilesController::class, 'rejectTalent'])->name('admin.talents.reject');

  // Company Management
  Route::get('/companies', [ProfilesController::class, 'listCompanies'])->name('admin.companies');
  Route::get('/companies/{user_profile}/create', [ProfilesController::class, 'createCompany'])->name('admin.companies.create');
  Route::post('/companies/create', [ProfilesController::class, 'storeCompany'])->name('admin.companies.store');
  Route::get('/companies/{company}', [ProfilesController::class, 'viewCompany'])->name('admin.companies.view');
  Route::get('/companies/{company}/edit', [ProfilesController::class, 'editCompany'])->name('admin.companies.edit');
  Route::post('/companies/{company}/edit', [ProfilesController::class, 'updateCompany'])->name('admin.companies.update');
  Route::delete('/companies/{company}', [ProfilesController::class, 'deleteCompany'])->name('admin.companies.destroy');
  Route::get('/companies/{company}/approve', [ProfilesController::class, 'approveCompany'])->name('admin.companies.approve');
  Route::get('/companies/{company}/reject', [ProfilesController::class, 'rejectCompany'])->name('admin.companies.reject');


  /* application */

  Route::get('/applications', [ApplicationController::class, 'index'])->name('admin.applications');
  Route::get('/applications/{application}/edit', [ApplicationController::class, 'edit'])->name('admin.applications.edit');
  Route::put('/applications/{application}', [ApplicationController::class, 'update'])->name('admin.applications.update');
  Route::delete('/applications/{application}', [ApplicationController::class, 'destroy'])->name('admin.applications.destroy');


  /* messages */

  Route::get('/messages', [MessageController::class, 'index'])->name('admin.messages');
  Route::get('/messages/{message}', [MessageController::class, 'show'])->name('admin.messages.show');
  Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('admin.messages.destroy');

  /* payment */

  Route::get('/payments', [PaymentController::class, 'index'])->name('admin.payments');
  Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('admin.payments.show');
  Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('admin.payments.destroy');
});
