<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Picture Upload -->
        <div>
            <x-input-label for="avatar" :value="__('Profile Picture')" />
            <div class="mt-2 flex items-center gap-4">
                <div class="flex-shrink-0">
                    <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden border-2 border-gray-300">
                        @if($user->avatar_url)
                            <img id="avatarPreview" src="{{ asset($user->avatar_url) }}" alt="{{ $user->full_name }}" class="h-full w-full object-cover">
                        @else
                            <span id="avatarPreviewText" class="text-2xl font-bold text-gray-600">
                                {{ strtoupper(substr($user->full_name ?? $user->username ?? 'U', 0, 1)) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex-1">
                    <input id="avatar" name="avatar" type="file" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" onchange="previewAvatar(this)">
                    <p class="mt-1 text-xs text-gray-500">{{ __('Upload a profile picture (JPG, PNG, GIF - Max 5MB)') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    @if($user->avatar_url)
                        <button type="button" onclick="removeAvatar()" class="mt-2 text-sm text-red-600 hover:text-red-800">{{ __('Remove Picture') }}</button>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="full_name" :value="__('Full Name')" />
            <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full" :value="old('full_name', $user->full_name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
        </div>

        <div>
            <x-input-label for="school" :value="__('School')" />
            <x-text-input id="school" name="school" type="text" class="mt-1 block w-full" :value="old('school', $user->school)" autocomplete="organization" />
            <x-input-error class="mt-2" :messages="$errors->get('school')" />
        </div>

        <div>
            <x-input-label for="class" :value="__('Class')" />
            <x-text-input id="class" name="class" type="text" class="mt-1 block w-full" :value="old('class', $user->class)" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('class')" />
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <x-textarea id="bio" name="bio" class="mt-1 block w-full" rows="4">{{ old('bio', $user->bio) }}</x-textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-100 cursor-not-allowed" :value="$user->email" disabled readonly />
            <p class="mt-1 text-sm text-gray-500">{{ __('Email cannot be changed.') }}</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    const previewText = document.getElementById('avatarPreviewText');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    if (previewText) {
                        previewText.style.display = 'none';
                    }
                    // Create img element if it doesn't exist
                    if (!preview) {
                        const container = input.closest('div').querySelector('.flex-shrink-0 > div');
                        const img = document.createElement('img');
                        img.id = 'avatarPreview';
                        img.src = e.target.result;
                        img.alt = 'Preview';
                        img.className = 'h-full w-full object-cover';
                        container.innerHTML = '';
                        container.appendChild(img);
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeAvatar() {
            if (confirm('{{ __("Are you sure you want to remove your profile picture?") }}')) {
                // Add hidden input to indicate removal
                const form = document.querySelector('form[action="{{ route('profile.update') }}"]');
                let removeInput = document.getElementById('remove_avatar');
                if (!removeInput) {
                    removeInput = document.createElement('input');
                    removeInput.type = 'hidden';
                    removeInput.name = 'remove_avatar';
                    removeInput.id = 'remove_avatar';
                    removeInput.value = '1';
                    form.appendChild(removeInput);
                }
                
                // Clear preview
                const preview = document.getElementById('avatarPreview');
                const previewText = document.getElementById('avatarPreviewText');
                const container = preview ? preview.closest('div') : null;
                
                if (preview) {
                    preview.remove();
                }
                if (container && previewText) {
                    previewText.style.display = 'flex';
                } else if (container) {
                    const initial = '{{ strtoupper(substr($user->full_name ?? $user->username ?? "U", 0, 1)) }}';
                    container.innerHTML = `<span id="avatarPreviewText" class="text-2xl font-bold text-gray-600">${initial}</span>`;
                }
                
                // Clear file input
                document.getElementById('avatar').value = '';
            }
        }
    </script>
</section>
