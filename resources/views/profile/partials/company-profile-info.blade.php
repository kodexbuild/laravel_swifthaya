@if ($user->userprofile->companyprofile)
@can('company', $user->userprofile->companyprofile->id)
<section>
  {{-- form --}}
  <header class="mb-4">
    <h2
      class="text-lg font-medium text-gray-900 dark:text-gray-100">
      {{ __('Company Profile Information') }}
    </h2>

    <p
      class="mt-1 text-sm text-gray-600 dark:text-gray-400">
      {{ __("Update your company profile information.") }}
    </p>
  </header>
  <div class="form-container">
    <form action="{{ route('company.update',$user->userprofile->companyprofile->id)}}"
      method="POST">
      @csrf
      @method("PATCH")
      <!-- Company Name -->
      <div class="form-group mb-4">
        <label class="block" for="company_name">Company Name</label>
        <input type="text" class="form-control"
          id="company_name" value="{{$user->userprofile->companyprofile->company_name}}" name="company_name" required
          maxlength="255">
      </div>

      <!-- Industry -->
      <div class="form-group mb-4">
        <label class="block" for="industry">Industry</label>
        <input type="text" class="form-control"
          id="industry" name="industry" value="{{$user->userprofile->companyprofile->industry}}" required
          maxlength="255">
      </div>

      <!-- Company Size -->
      <div class="form-group mb-4">
        <label class="block" for="company_size">Company Size</label>
        <input type="number" class="form-control"
          id="company_size" name="company_size" value="{{$user->userprofile->companyprofile->company_size}}" required
          min="1">
      </div>

      <!-- Founded Year -->
      <div class="form-group mb-4">
        <label class="block" for="founded_year">Founded Year</label>
        <input type="number" class="form-control"
          id="founded_year" name="founded_year" value="{{$user->userprofile->companyprofile->founded_year}}" required
          min="1800" max="{{ date('Y') }}">
      </div>
     <button class="bg-blue-900 text-white px-8 py-2 rounded mt-4" type="submit">Save</button>
    </form>
  </div>


</section>
@endcan
    
@endif