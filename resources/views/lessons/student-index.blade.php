<x-app-layout>
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 px-4 sm:px-0">
            <h1 class="text-3xl font-extrabold text-[#2454FF] tracking-tight">Available Lessons</h1>
            <p class="text-gray-600 mt-2">Browse and access all published lessons</p>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($lessons as $lesson)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
                        <h3 class="text-xl font-semibold text-[#2454FF] mb-2">{{ $lesson->title }}</h3>
                        <p class="text-gray-600 text-sm mb-2">Topic: {{ $lesson->topic }}</p>
                        @if($lesson->duration)
                        <p class="text-gray-500 text-sm mb-4">Duration: {{ $lesson->duration }} mins</p>
                        @endif
                        <a href="{{ route('lesson.show', $lesson->id) }}" 
                           class="inline-block bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150">
                            View Lesson
                        </a>
                    </div>
                    @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500 text-lg">No lessons available at the moment.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

