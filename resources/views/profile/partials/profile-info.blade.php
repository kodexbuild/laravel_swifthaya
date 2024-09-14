<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>
    @if (session('status') === 'profile-updated')
    <p
      x-data="{ show: true }"
      x-show="show"
      x-transition
      x-init="setTimeout(() => show = false, 2000)"
      class="text-sm text-white dark:text-gray-400 bg-green-500 p-4 rounded border border-green-800">
        {{ __('Saved.') }}
     </p>
    @endif
    @if(session('error'))
    <div
      class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-6"
      role="alert">
      <strong class="font-bold">{{ session('error')
        }}</strong>
    </div>
    @endif

    <form method="post" action="{{ route('user.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')
        <div>
            <x-input-label for="first_name" :value="__('First Name')" />
            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->userprofile->first_name)" required autofocus autocomplete="first_name" />
            <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
        </div>

        <div>
            <x-input-label for="last_name" :value="__('Last Name')" />
            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name',  $user->userprofile->last_name)" required autofocus autocomplete="last_name" />
            <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
          <x-input-label for="bio" :value="__('Bio')" />
          <x-text-input id="bio" name="bio" type="text" class="mt-1 block w-full" :value="old('bio',  $user->userprofile->bio)" autofocus autocomplete="bio" />
          <x-input-error class="mt-2" :messages="$errors->get('bio')" />
      </div>
        <div>
          <x-input-label for="location" :value="__('Location Name')" />
          <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location',  $user->userprofile->location)" autofocus autocomplete="location" />
          <x-input-error class="mt-2" :messages="$errors->get('location')" />
      </div>
        <div>
          <x-input-label for="phone_number" :value="__('Phone Number')" />
          <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" :value="old('phone_number',  $user->userprofile->phone_number)" autofocus autocomplete="phone_number" />
          <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
      </div>
        <div>
          <x-input-label for="website" :value="__('Website Name')" />
          <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website',  $user->userprofile->website)" autofocus autocomplete="website" />
          <x-input-error class="mt-2" :messages="$errors->get('website')" />
      </div>
      <button class="bg-blue-900 text-white px-8 py-2 rounded mt-4" type="submit">Save</button>
           
    </form>
</section>
