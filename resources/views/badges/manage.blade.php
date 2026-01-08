<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                {{-- Header --}}
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-extrabold text-[#2454FF]">
                        Urus Lencana Saya
                    </h2>
                    <a href="{{ route('badges.create') }}" 
                       class="bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-150 ease-in-out">
                        Cipta Lencana
                    </a>
                </div>

                {{-- Success/Error Messages --}}
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Badges List --}}
                @if($badges->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($badges as $badge)
                            <div class="border-2 rounded-lg p-6 hover:shadow-lg transition duration-150" 
                                 style="border-color: {{ $badge->color }};">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <span class="text-5xl mr-3">{{ $badge->icon }}</span>
                                        <div>
                                            <h3 class="text-xl font-bold" style="color: {{ $badge->color }};">
                                                {{ $badge->name }}
                                            </h3>
                                            <p class="text-sm text-gray-600">{{ $badge->xp_reward }} XP</p>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-gray-700 mb-4">{{ $badge->description }}</p>

                                {{-- Activity Assignment Status --}}
                                <div class="mb-4 p-3 bg-gray-50 rounded">
                                    @if($badge->activity)
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-link text-green-600"></i>
                                            Ditugaskan kepada: <strong>{{ $badge->activity->title }}</strong>
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-500">
                                            <i class="fas fa-unlink"></i>
                                            Tidak Ditugaskan
                                        </p>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="flex gap-2">
                                    <a href="{{ route('badges.edit', $badge->id) }}" 
                                       class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded text-center transition">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('badges.destroy', $badge->id) }}" method="POST" class="flex-1"
                                          onsubmit="return confirm('Adakah anda pasti mahu memadam lencana ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded transition">
                                            <i class="fas fa-trash"></i> Padam
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🏆</div>
                        <h3 class="text-2xl font-bold text-gray-700 mb-2">Tiada Lencana Lagi</h3>
                        <p class="text-gray-600 mb-6">Cipta lencana pertama anda untuk aktiviti!</p>
                        <a href="{{ route('badges.create') }}" 
                           class="inline-block bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                            Cipta Lencana Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
