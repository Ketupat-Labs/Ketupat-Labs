<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                {{-- Header --}}
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-extrabold text-[#2454FF]">
                        Cipta Lencana Baru
                    </h2>
                    <a href="{{ route('badges.manage') }}" 
                       class="text-gray-600 hover:text-gray-900 font-medium">
                        ← Kembali
                    </a>
                </div>

                {{-- Form --}}
                <form action="{{ route('badges.store') }}" method="POST">
                    @csrf

                    {{-- Name --}}
                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                            Nama Lencana <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror"
                               placeholder="cth: Juara Matematik">
                        @error('name')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-6">
                        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                            Penerangan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" id="description" rows="3" required
                                  class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror"
                                  placeholder="Penerangan tentang lencana ini...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Icon (Emoji Picker) --}}
                    <div class="mb-6">
                        <label for="icon" class="block text-gray-700 text-sm font-bold mb-2">
                            Ikon Lencana <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="icon" id="icon" value="{{ old('icon', '🏆') }}" required maxlength="10"
                               class="shadow appearance-none border rounded w-full py-3 px-4 text-4xl text-center leading-tight focus:outline-none focus:shadow-outline @error('icon') border-red-500 @enderror"
                               placeholder="🏆">
                        @error('icon')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                        
                        {{-- Emoji Quick Picker --}}
                        <div class="mt-3">
                            <p class="text-sm text-gray-600 mb-2">Pilih emoji atau taip sendiri:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['🏆', '⭐', '🎯', '🎮', '💎', '🥇', '🥈', '🥉', '👑', '🔥', '💪', '🎓', '📚', '✨', '🌟', '💡', '🚀', '🎨', '🎵', '⚡'] as $emoji)
                                    <button type="button" onclick="document.getElementById('icon').value='{{ $emoji }}'" 
                                            class="text-3xl hover:scale-125 transition-transform duration-150 p-2 hover:bg-gray-100 rounded">
                                        {{ $emoji }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Color --}}
                    <div class="mb-6">
                        <label for="color" class="block text-gray-700 text-sm font-bold mb-2">
                            Warna Lencana <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="color" name="color" id="color" value="{{ old('color', '#2454FF') }}" required
                                   class="h-12 w-20 border-2 border-gray-300 rounded cursor-pointer">
                            <input type="text" id="color-hex" value="{{ old('color', '#2454FF') }}" readonly
                                   class="shadow appearance-none border rounded py-3 px-4 text-gray-700 bg-gray-50">
                        </div>
                        @error('color')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- XP Reward --}}
                    <div class="mb-6">
                        <label for="xp_reward" class="block text-gray-700 text-sm font-bold mb-2">
                            Ganjaran XP <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="xp_reward" id="xp_reward" value="{{ old('xp_reward', 100) }}" 
                               min="0" max="1000" required
                               class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('xp_reward') border-red-500 @enderror">
                        <p class="text-sm text-gray-600 mt-1">Antara 0 hingga 1000 XP</p>
                        @error('xp_reward')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Preview --}}
                    <div class="mb-6 p-6 bg-gray-50 rounded-lg border-2" id="preview-box" style="border-color: #2454FF;">
                        <p class="text-sm text-gray-600 mb-3 font-semibold">Pratonton:</p>
                        <div class="flex items-center">
                            <span id="preview-icon" class="text-6xl mr-4">🏆</span>
                            <div>
                                <h3 id="preview-name" class="text-2xl font-bold" style="color: #2454FF;">Nama Lencana</h3>
                                <p id="preview-desc" class="text-gray-600">Penerangan lencana akan muncul di sini</p>
                                <p class="text-sm text-gray-500 mt-1"><span id="preview-xp">100</span> XP</p>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex items-center justify-between">
                        <a href="{{ route('badges.manage') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition">
                            Batal
                        </a>
                        <button type="submit" 
                                class="bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                            Cipta Lencana
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Live Preview Script --}}
    <script>
        // Update color hex display
        document.getElementById('color').addEventListener('input', function() {
            document.getElementById('color-hex').value = this.value;
            document.getElementById('preview-box').style.borderColor = this.value;
            document.getElementById('preview-name').style.color = this.value;
        });

        // Update preview
        document.getElementById('name').addEventListener('input', function() {
            document.getElementById('preview-name').textContent = this.value || 'Nama Lencana';
        });

        document.getElementById('description').addEventListener('input', function() {
            document.getElementById('preview-desc').textContent = this.value || 'Penerangan lencana akan muncul di sini';
        });

        document.getElementById('icon').addEventListener('input', function() {
            document.getElementById('preview-icon').textContent = this.value || '🏆';
        });

        document.getElementById('xp_reward').addEventListener('input', function() {
            document.getElementById('preview-xp').textContent = this.value || '0';
        });
    </script>
</x-app-layout>
