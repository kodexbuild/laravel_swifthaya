<x-app-layout>
  <x-slot name="header">
    <h2
      class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Project Details') }}
    </h2>
  </x-slot>


  <div class="container mx-auto py-12">
    <!-- Profile Header -->
    <div class="bg-white shadow rounded-lg p-8">
      <div class="flex items-center">
        <div class="w-24 h-24 rounded-full overflow-hidden">
          @if($user_profile->getImgUrl())
          <img
            src="{{ $user_profile->getImgUrl()}}"
            alt="Profile Picture"
            class="w-full h-full object-cover">
          @else
          <img src="https://via.placeholder.com/150"
            alt="Profile Picture"
            class="w-full h-full object-cover">
          @endif
        </div>
        <div class="ml-6">
          <h1 class="text-3xl font-bold text-gray-900">{{
           $user_profile->companyprofile->company_name}}</h1>

          <p class="text-xl text-gray-600 mt-2">
            {{ ucfirst($user_profile->user->user_type) }}
          </p>
          <p class="text-gray-500 mt-4">Location: {{
            $user_profile->location ?? 'Not
            specified' }}
          </p>
        </div>
        <div class="ml-auto">
          <a href="{{Route("talent.project.apply",[$project->id,  Auth::user()->id])}}"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg text-lg hover:bg-blue-500 transition">
            Apply
          </a>
        </div>
      </div>
    </div>

    <!-- Profile Details -->
    <div class="mt-12">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Title Section -->
        <div class="bg-white shadow rounded-lg p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">
            Project Title</h2>
          <ul
            class="list-disc list-inside text-gray-700 space-y-2">
            <li>{{ $project->title }}</li>
          </ul>
        </div>

        <!-- Required_skills Section -->
        <div class="bg-white shadow rounded-lg p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">
            Required Skills</h2>
          <ul
            class="list-disc list-inside text-gray-700 space-y-2">
            <li>{{ $project->required_skills }}</li>
          </ul>
        </div>

        <!-- duration Section -->
        <div class="bg-white shadow rounded-lg p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">
            Duration</h2>
          <ul
            class="list-disc list-inside text-gray-700 space-y-2">
            <li>{{ $project->duration }} years</li>
          </ul>
        </div>

        {{-- description --}}
        <div class="bg-white shadow rounded-lg p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">
            Project description</h2>
          <ul
            class="list-disc list-inside text-gray-700 space-y-2">
            <li>{{ $project->description }}</li>
          </ul>
        </div>


        <!-- budget Section -->
        <div class="bg-white shadow rounded-lg p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">
            Budget</h2>
          <ul
            class="list-disc list-inside text-gray-700 space-y-2">
            <li>{{"$" . $project->budget}}</li>
          </ul>
        </div>


        <!-- Website Section -->
        <div class="bg-white shadow rounded-lg p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">
            Website</h2>
          <ul
            class="list-disc list-inside text-gray-700 space-y-2">
            <li><a href="{{$user_profile->website}}"
                target="_blank"
                class="text-blue-600 hover:underline">{{$user_profile->website}}</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>