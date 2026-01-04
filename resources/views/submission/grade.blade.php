<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Grade Submission') }}
            </h2>
            <a href="{{ route('submission.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                &larr; Back to Submissions
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Left Column: Submission Details & File -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Submission Details</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700">Student</label>
                        <p class="text-gray-900">{{ $submission->user->full_name ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-500">{{ $submission->user->email ?? '' }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700">Assignment</label>
                        <p class="text-gray-900">{{ $submission->assignment_name }}</p>
                        <p class="text-sm text-gray-500">Submitted: {{ $submission->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>

                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200 text-center">
                        <p class="text-sm text-gray-600 mb-2">Student File:</p>
                        @if($submission->file_path)
                            <div class="mb-4">
                                <span
                                    class="font-mono text-xs bg-gray-200 px-2 py-1 rounded">{{ $submission->file_name }}</span>
                            </div>
                            <a href="{{ route('submission.file', $submission->id) }}" target="_blank"
                                class="inline-block bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                                View / Download File
                            </a>
                        @else
                            <p class="text-red-500 font-bold">No file uploaded.</p>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Grading Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Grading</h3>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('submission.grade', $submission->id) }}" method="POST">
                        @csrf

                        <div class="mb-6">
                            <label for="grade" class="block text-gray-700 text-sm font-bold mb-2">Grade (0-100)</label>
                            <input type="number" name="grade" id="grade" placeholder="Enter grade..." min="0" max="100"
                                required value="{{ old('grade', $submission->grade) }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg font-bold text-center w-32">
                        </div>

                        <div class="mb-6">
                            <label for="feedback" class="block text-gray-700 text-sm font-bold mb-2">Feedback
                                (Optional)</label>
                            <textarea name="feedback" id="feedback" rows="6"
                                placeholder="Write feedback for the student here..."
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('feedback', $submission->feedback) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit"
                                class="bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-8 rounded focus:outline-none focus:shadow-outline transform transition hover:scale-105 duration-150">
                                Submit Grade
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>