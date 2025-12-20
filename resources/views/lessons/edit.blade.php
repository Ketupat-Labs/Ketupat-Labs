<x-app-layout>
<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Lesson</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $lesson->title }}</p>
            </div>
            <a href="{{ route('lessons.index') }}" class="text-gray-600 hover:text-gray-900 font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Lessons
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6">
                <strong class="font-bold">Validation Error!</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('lessons.update', $lesson->id) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- SECTION 1: LESSON INFORMATION -->
            <div class="space-y-6">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h2 class="text-xl font-semibold text-gray-900">Lesson Information</h2>
                    <p class="text-sm text-gray-600 mt-1">Basic details and metadata about your lesson</p>
                </div>

                <!-- Title and Topic -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block font-medium text-gray-900 mb-2">
                            Lesson Title <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="title" id="title" required
                               class="block w-full border-gray-300 rounded-lg shadow-sm p-3 focus:border-blue-500 focus:ring focus:ring-blue-500/20 transition"
                               value="{{ old('title', $lesson->title) }}">
                    </div>

                    <div>
                        <label for="topic" class="block font-medium text-gray-900 mb-2">
                            Module / Topic <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="topic" id="topic" list="topics" required
                               class="block w-full border-gray-300 rounded-lg shadow-sm p-3 focus:border-blue-500 focus:ring focus:ring-blue-500/20 transition"
                               value="{{ old('topic', $lesson->topic) }}" 
                               placeholder="Select or type a topic">
                        <datalist id="topics">
                            <option value="HCI">3.1 Interaction Design</option>
                            <option value="HCI_SCREEN">3.2 Screen Design</option>
                            <option value="Algorithm">Other: Algorithms</option>
                        </datalist>
                    </div>
                </div>

                <!-- Duration and URL -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="duration" class="block font-medium text-gray-900 mb-2">
                            Estimated Duration (Minutes)
                        </label>
                        <input type="number" name="duration" id="duration" min="5"
                               class="block w-full border-gray-300 rounded-lg shadow-sm p-3 focus:border-blue-500 focus:ring focus:ring-blue-500/20 transition"
                               value="{{ old('duration', $lesson->duration) }}"
                               placeholder="e.g., 45">
                    </div>

                    <div>
                        <label for="url" class="block font-medium text-gray-900 mb-2">
                            External Resource URL
                        </label>
                        <input type="url" name="url" id="url"
                               class="block w-full border-gray-300 rounded-lg shadow-sm p-3 focus:border-blue-500 focus:ring focus:ring-blue-500/20 transition"
                               value="{{ old('url', $lesson->url) }}" 
                               placeholder="https://example.com">
                    </div>
                </div>

                <!-- Material File Upload -->
                <div>
                    <label for="material" class="block font-medium text-gray-900 mb-2">
                        Material File
                    </label>
                    @if($lesson->material_path)
                        <div class="mb-2 text-sm text-gray-600">
                            Current file: <a href="{{ Storage::url($lesson->material_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ basename($lesson->material_path) }}</a>
                        </div>
                    @endif
                    <input type="file" name="material" id="material"
                           class="block w-full text-sm text-gray-600
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-lg file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100 transition">
                    <p class="mt-2 text-xs text-gray-500">Upload a new file to replace the current one (optional)</p>
                </div>
            </div>

            <!-- SECTION 2: LESSON CONTENT -->
            <div class="space-y-4">
                <div class="border-l-4 border-green-500 pl-4">
                    <h2 class="text-xl font-semibold text-gray-900">Lesson Content</h2>
                    <p class="text-sm text-gray-600 mt-1">Build your lesson using blocks - add text, headings, images, videos, and interactive games</p>
                </div>

                <!-- React Block Editor Container -->
                <div id="block-editor-root" data-initial-blocks="{{ json_encode($lesson->content_blocks['blocks'] ?? []) }}"></div>
                
                <!-- Hidden input to store block data -->
                <input type="hidden" name="content_blocks" id="content_blocks_input" required>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-300">
                <a href="{{ route('lessons.index') }}" class="text-gray-600 hover:text-gray-900 font-medium px-4 py-2 rounded-lg hover:bg-gray-100 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition duration-150 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Lesson
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
