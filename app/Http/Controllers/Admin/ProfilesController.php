<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompany_profileRequest;
use App\Http\Requests\StoreTalent_profileRequest;
use App\Http\Requests\UpdateTalent_profileRequest;
use App\Models\Company_profile;
use App\Models\Talent_profile;
use App\Models\User_profile;
use Illuminate\Http\Request;

class aProfilesController extends Controller
{
  /* talent */
  // List all Talent Profiles
  public function listTalents()
  {
    $talent_profiles = Talent_profile::all();
    return view('admin.talents.index', compact('talent_profiles'));
  }
  public function createTalent(User_profile $user_profile)
  {
    return view('admin.talents.create', compact("user_profile"));
  }
  public function storeTalent(User_profile $user_profile)
  {
    // $validated =  $request->validated();
    $validated["user_profile_id"] = $user_profile->id;
    // Talent_profile::create($validated);


    $skillsArray = explode(',', request()->skills);
    $experienceArray = request()->experience;
    $educationArray = request()->education;
    $portfolioArray = request()->portfolio;
    // dd(json_encode($experienceArray), json_encode($skillsArray));
    // dd(json_encode($experienceArray));

    $talent_profile = Talent_profile::create(
      [
        'user_profile_id' => $user_profile->id,
        'skills' => json_encode($skillsArray),
        'experience' => json_encode($experienceArray),
        'education' => json_encode($educationArray),
        'portfolio' => json_encode($portfolioArray)
      ]
    );
    return redirect()->route('admin.talents')->with('success', 'Talent profile created successfully.');
  }

  // View Talent Profile Details
  public function viewTalent(Talent_profile $talent)
  {
    $profile = Talent_profile::findOrFail($talent->id);
    return view('admin.talents.view', compact('profile'));
  }

  // Edit Talent Profile
  public function editTalent(Talent_profile $talent)
  {
    $profile = Talent_profile::findOrFail($talent->id);
    return view('admin.talents.edit', compact('profile'));
  }

  // Update Talent Profile
  public function updateTalent( Talent_profile $talent)
  {
    // $validated =  $request->validated();
    $profile = Talent_profile::findOrFail($talent->id);
    $skillsArray = explode(',', request()->skills);
    $experienceArray = request()->experience;
    $educationArray = request()->education;
    $portfolioArray = request()->portfolio;
    // dd(json_encode($experienceArray), json_encode($skillsArray));
    // dd(json_encode($educationArray));


    $profile->update(
      [
        'skills' => json_encode($skillsArray),
        'experience' => json_encode($experienceArray),
        'education' => json_encode($educationArray),
        'portfolio' => json_encode($portfolioArray)
      ]
    );
    return redirect()->route('admin.talents')->with('success', 'Talent profile updated successfully.');
  }

  // Delete Talent Profile
  public function deleteTalent(Talent_profile $talent)
  {
    // dd($talent->userprofile->user->id);
    // $profile = Talent_profile::findOrFail($talent->id);
    $talent->delete();
    $talent->userprofile->user->delete();
    return redirect()->route('admin.talents')->with('success', 'Talent profile deleted successfully.');
  }

  // Approve Talent Profile
  public function approveTalent(Talent_profile $talent)
  {
    $profile = Talent_profile::findOrFail($talent->id);
    $profile->status = 'approved';
    $profile->save();

    return redirect()->route('admin.talents')->with('success', 'Talent profile has been approved successfully.');
  }

  // Reject Talent Profile
  public function rejectTalent(Talent_profile $talent)
  {
    $profile = Talent_profile::findOrFail($talent->id);
    $profile->status = 'rejected';
    $profile->save();

    return redirect()->route('admin.talents')->with('error', 'Talent profile has been rejected.');
  }


  /* company */
  // List all Company Profiles
  public function listCompanies()
  {
    $company_profiles = Company_profile::all();
    return view('admin.companies.index', compact('company_profiles'));
  }
  public function createCompany(User_profile $user_profile)
  {
    $hasProfile = Company_profile::where("user_profile_id", $user_profile->id)->exists();
    $company = Company_profile::where("user_profile_id", $user_profile->id)->first();
    if ($hasProfile) {
      return redirect()->route("admin.companies.view", $company->id);
    }
    return view('admin.companies.create', compact("user_profile"));
  }
  public function storeCompany(StoreCompany_profileRequest $request, User_profile $user_profile)
  {
    // dd($user_profile);
    $validated =  $request->validated();
    $validated["user_profile_id"] = $user_profile->id;
    Company_profile::create($validated);
    return redirect()->route('admin.companies')->with('success', 'Company profile created successfully.');
  }
  // View Company Profile Details
  public function viewCompany(Company_profile $company)
  {
    $company = Company_profile::findOrFail($company->id);
    return view('admin.companies.view', compact('company'));
  }

  // Edit Company Profile
  public function editCompany(Company_profile $company)
  {
    $profile = Company_profile::findOrFail($company->id);
    return view('admin.companies.edit', compact('profile'));
  }

  // Update Company Profile
  public function updateCompany(StoreCompany_profileRequest $request, Company_profile $company)
  {
    $validated =  $request->validated();
    $profile = Company_profile::findOrFail($company->id);
    $profile->update($validated);
    return redirect()->route('admin.companies')->with('success', 'Company profile updated successfully.');
  }

  // Delete Company Profile
  public function deleteCompany(Company_profile $company)
  {
    $company->delete();
    $company->userprofile->user->delete();
    return redirect()->route('admin.companies')->with('success', 'Company profile deleted successfully.');
  }

  // Approve Company Profile
  public function approveCompany(Company_profile $company)
  {
    $profile = Company_profile::findOrFail($company->id);
    $profile->status = 'approved';
    $profile->save();

    return redirect()->route('admin.companies')->with('success', 'Company profile has been approved successfully.');
  }

  // Reject Company Profile
  public function rejectCompany(Company_profile $company)
  {
    $profile = Company_profile::findOrFail($company->id);
    $profile->status = 'rejected';
    $profile->save();

    return redirect()->route('admin.companies')->with('error', 'Company profile has been rejected.');
  }
}
