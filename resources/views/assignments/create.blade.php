<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Lessons') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('assignments.store') }}">
                        @csrf

                        <!-- Class Selection -->
                        <div class="mb-6">
                            <label for="classroom_id" class="block text-sm font-medium text-gray-700 mb-2">Select Target
                                Class</label>
                            <select name="classroom_id" id="classroom_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Choose Class --</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}">{{ $classroom->name }} ({{ $classroom->subject }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lesson Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Lessons to Assign</label>
                            <div class="border rounded-md p-4 max-h-60 overflow-y-auto bg-gray-50">
                                @foreach($lessons as $lesson)
                                    <div
                                        class="flex items-start mb-3 pb-3 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0">
                                        <div class="flex items-center h-5">
                                            <input id="lesson_{{ $lesson->id }}" name="lessons[]" value="{{ $lesson->id }}"
                                                type="checkbox"
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="lesson_{{ $lesson->id }}"
                                                class="font-medium text-gray-700">{{ $lesson->title }}</label>
                                            <p class="text-gray-500">{{ $lesson->topic }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Assign Selected Lessons') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>