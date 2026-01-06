<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informasi Peribadi') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Kemaskini maklumat profil akaun anda.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Picture Upload -->
        <div>
            <x-input-label for="avatar" :value="__('Gambar Profil')" />
            <div class="mt-2 flex items-center gap-4">
                <div class="flex-shrink-0">
                    <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden border-2 border-gray-300" id="avatarContainer">
                        @if($user->avatar_url)
                            <img id="avatarPreview" src="{{ asset($user->avatar_url) }}" alt="{{ $user->full_name }}" class="h-full w-full object-cover" onerror="this.style.display='none'; document.getElementById('avatarPreviewText').style.display='flex';">
                            <span id="avatarPreviewText" class="text-2xl font-bold text-gray-600" style="display: none;">
                                {{ strtoupper(substr($user->full_name ?? $user->username ?? 'U', 0, 1)) }}
                            </span>
                        @else
                            <span id="avatarPreviewText" class="text-2xl font-bold text-gray-600" style="display: flex;">
                                {{ strtoupper(substr($user->full_name ?? $user->username ?? 'U', 0, 1)) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center">
                        <label for="avatar" class="cursor-pointer bg-blue-50 text-blue-700 font-semibold py-2 px-4 rounded-md hover:bg-blue-100 transition ease-in-out duration-150 text-sm mr-4 border-0">
                            {{ __('Pilih Fail') }}
                        </label>
                        <span id="fileName" class="text-sm text-gray-500">{{ __('Tiada fail dipilih') }}</span>
                        <input id="avatar" name="avatar" type="file" accept="image/*" class="hidden" onchange="previewAvatar(this); updateFileName(this)">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Muat naik gambar profil (JPG, PNG, GIF - Maks 5MB)') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    @if($user->avatar_url)
                        <button type="button" onclick="removeAvatar()" class="mt-2 text-sm text-red-600 hover:text-red-800">{{ __('Buang Gambar') }}</button>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="full_name" :value="__('Nama Penuh')" />
            <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full" :value="old('full_name', $user->full_name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
        </div>

        <div>
            <x-input-label for="school" :value="__('Sekolah')" />
            <x-text-input id="school" name="school" type="text" class="mt-1 block w-full" :value="old('school', $user->school)" autocomplete="organization" />
            <x-input-error class="mt-2" :messages="$errors->get('school')" />
        </div>

        <div>
            <x-input-label for="class" :value="__('Kelas')" />
            <x-text-input id="class" name="class" type="text" class="mt-1 block w-full" :value="old('class', $user->class)" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('class')" />
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <x-textarea id="bio" name="bio" class="mt-1 block w-full" rows="4">{{ old('bio', $user->bio) }}</x-textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Emel')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-100 cursor-not-allowed" :value="$user->email" disabled readonly />
            <p class="mt-1 text-sm text-gray-500">{{ __('Emel tidak boleh ditukar.') }}</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Disimpan.') }}</p>
            @endif
        </div>
    </form>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.querySelector('.flex-shrink-0 > div');
                    const preview = document.getElementById('avatarPreview');
                    const previewText = document.getElementById('avatarPreviewText');
                    
                    if (preview) {
                        // Update existing image
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    } else {
                        // Create new image element
                        const img = document.createElement('img');
                        img.id = 'avatarPreview';
                        img.src = e.target.result;
                        img.alt = 'Preview';
                        img.className = 'h-full w-full object-cover';
                        img.style.display = 'block';
                        
                        // Clear container and add image
                        if (container) {
                            container.innerHTML = '';
                            container.appendChild(img);
                        }
                    }
                    
                    // Hide text preview if it exists
                    if (previewText) {
                        previewText.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeAvatar() {
            if (confirm('{{ __("Adakah anda pasti mahu membuang gambar profil anda?") }}')) {
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
                document.getElementById('fileName').textContent = '{{ __("Tiada fail dipilih") }}';
            }
        }

        function updateFileName(input) {
            const fileNameSpan = document.getElementById('fileName');
            if (input.files && input.files.length > 0) {
                fileNameSpan.textContent = input.files[0].name;
            } else {
                fileNameSpan.textContent = '{{ __("Tiada fail dipilih") }}';
            }
        }
    </script>
</section>
