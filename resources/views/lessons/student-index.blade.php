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
                    @forelse ($items as $item)
                        @if($item->item_type === 'activity')
                             {{-- ACTIVITY CARD --}}
                            <div class="border border-purple-200 bg-purple-50 rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-xl font-semibold text-purple-700">{{ $item->title }}</h3>
                                    <span class="bg-purple-200 text-purple-800 text-xs font-bold px-2 py-1 rounded uppercase">Activity</span>
                                </div>
                                <p class="text-gray-600 text-sm mb-2 font-medium">{{ $item->type }}</p>
                                <p class="text-gray-500 text-sm mb-4">Duration: {{ $item->suggested_duration }}</p>
                                @if($item->due_date)
                                     <p class="text-red-500 text-sm font-bold mb-4">Due: {{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}</p>
                                @endif
                                <a href="{{ route('activities.show', $item->id) }}" 
                                   class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150">
                                    View Activity
                                </a>
                            </div>
                        @else
                            {{-- LESSON CARD --}}
                            <div class="border border-gray-200 bg-white rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <h3 class="text-xl font-semibold text-[#2454FF] mb-2">{{ $item->title }}</h3>
                                <p class="text-gray-600 text-sm mb-2">Topic: {{ $item->topic }}</p>
                                @if($item->duration)
                                <p class="text-gray-500 text-sm mb-4">Duration: {{ $item->duration }} mins</p>
                                @endif
                                <a href="{{ route('lesson.show', $item->id) }}" 
                                   class="inline-block bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150">
                                    View Lesson
                                </a>
                            </div>
                        @endif
                    @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500 text-lg">No content available at the moment.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

