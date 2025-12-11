<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Butiran Aktiviti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <a href="{{ url()->previous() }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
                    </div>

                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $activity->title }}</h1>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">{{ $activity->type }}</span>
                        <span class="text-gray-600 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $activity->suggested_duration }}
                        </span>
                    </div>

                    <div class="prose max-w-none">
                        <h3 class="text-lg font-semibold mb-2">Penerangan</h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ $activity->description }}</p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <form action="{{ url()->previous() }}">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                                Selesai / Kembali
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
