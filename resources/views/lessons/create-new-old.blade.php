<x-app-layout>
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6 border-b-2 border-[#2454FF] pb-2">
                <h2 class="text-2xl font-bold text-gray-800">
                    Create New Lesson
                </h2>
                <a href="{{ route('lessons.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                    ← Back to Lessons
                </a>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <strong class="font-bold">Validation Error!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('lessons.store') }}" class="space-y-6">
                @csrf

                <!-- Title and Topic Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block font-medium text-lg text-[#3E3E3E]">
                            Lesson Title <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="title" id="title" required
                               class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                               value="{{ old('title') }}">
                    </div>

                    <div>
                        <label for="topic" class="block font-medium text-lg text-[#3E3E3E]">
                            Module / Topic <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="topic" id="topic" list="topics" required
                               class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                               value="{{ old('topic') }}" placeholder="Select or type a topic">
                        <datalist id="topics">
                            <option value="HCI">3.1 Interaction Design</option>
                            <option value="HCI_SCREEN">3.2 Screen Design</option>
                            <option value="Algorithm">Other: Algorithms</option>
                        </datalist>
                    </div>
                </div>

                <!-- Block Editor Section -->
                <div>
                    <label class="block font-medium text-lg text-[#3E3E3E] mb-3">
                        Lesson Content <span class="text-red-600">*</span>
                    </label>
                    <p class="text-sm text-gray-600 mb-4">
                        Build your lesson using blocks. Add text, headings, YouTube videos, and images by clicking the blocks in the sidebar.
                    </p>
                    
                    <!-- React Block Editor Container -->
                    <div id="block-editor-root" data-initial-blocks="[]"></div>
                    
                    <!-- Hidden input to store block data -->
                    <input type="hidden" name="content_blocks" id="content_blocks_input" required>
                </div>

                <!-- Optional Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200">
                    <div>
                        <label for="duration" class="block font-medium text-lg text-[#3E3E3E]">
                            Estimated Duration (Minutes)
                        </label>
                        <input type="number" name="duration" id="duration" min="5"
                               class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                               value="{{ old('duration') }}">
                    </div>

                    <div>
                        <label for="url" class="block font-medium text-lg text-[#3E3E3E]">
                            External Resource URL (Optional)
                        </label>
                        <input type="url" name="url" id="url"
                               class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                               value="{{ old('url') }}" placeholder="https://example.com">
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-start space-x-4 pt-6 border-t border-gray-200">
                    <button type="submit" class="bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition ease-in-out duration-150">
                        Save Lesson
                    </button>
                    <a href="{{ route('lessons.index') }}" class="text-gray-600 hover:text-gray-900 font-medium px-4">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
</x-app-layout>

