<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Kelas Saya') }}
            </h2>
            @if($currentUser->role === 'teacher')
                <a href="{{ route('classrooms.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 shadow flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Cipta Kelas
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($classrooms as $classroom)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $classroom->name }}</h3>
                            <p class="text-gray-600 mb-4">{{ $classroom->subject }}</p>
                            @if($classroom->year)
                                <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded mb-4">
                                    Tahun: {{ $classroom->year }}
                                </span>
                            @endif

                            <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">
                                    {{ $classroom->students_count ?? $classroom->students()->count() }} Pelajar
                                </span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('classrooms.show', $classroom) }}"
                                        class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded-full transition"
                                        title="Lihat Kelas">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($currentUser->role === 'teacher')
                                        <a href="{{ route('classrooms.edit', $classroom) }}"
                                            class="text-green-600 hover:text-green-800 p-2 hover:bg-green-50 rounded-full transition"
                                            title="Kemaskini Kelas">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('classrooms.destroy', $classroom) }}"
                                            onsubmit="return confirm('Adakah anda pasti mahu memadam kelas ini?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-full transition" title="Padam Kelas">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                            <p class="text-gray-500">Tiada kelas ditemui.</p>
                            @if($currentUser->role === 'teacher')
                                <a href="{{ route('classrooms.create') }}"
                                    class="text-blue-600 hover:underline mt-2 inline-block font-semibold">
                                    + Cipta kelas pertama anda
                                </a>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>