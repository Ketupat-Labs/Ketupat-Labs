<x-app-layout>
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 card">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b-2 border-[#FFBA08] pb-2">
                Edit Lesson: {{ $lesson->title }}
            </h2>

            {{-- Form action points to the LessonController@update method --}}
            <form method="POST" action="{{ route('lessons.update', $lesson->id) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT') {{-- Required for the UPDATE method --}}

                <div>
                    <label for="title" class="block font-medium text-lg text-[#3E3E3E]">Lesson Title <span class="text-red-600">*</span></label>
                    <input type="text" name="title" id="title" required
                           class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                           value="{{ old('title', $lesson->title) }}">
                </div>

                <div>
                    <label for="topic" class="block font-medium text-lg text-[#3E3E3E]">Module / Topic <span class="text-red-600">*</span></label>
                    <input type="text" name="topic" id="topic" list="topics" required
                           class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                           value="{{ old('topic', $lesson->topic) }}" placeholder="Select or type a topic">
                    <datalist id="topics">
                        <option value="HCI">3.1 Interaction Design</option>
                        <option value="HCI_SCREEN">3.2 Screen Design</option>
                        <option value="Algorithm">Other: Algorithms</option>
                    </datalist>
                </div>

                <div>
                    <label for="content" class="block font-medium text-lg text-[#3E3E3E]">Lesson Content (Context) <span class="text-red-600">*</span></label>
                    <textarea name="content" id="content" rows="6" required
                              class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                              placeholder="Write the lesson content here...">{{ old('content', $lesson->content) }}</textarea>
                </div>
                
                <div>
                    <label for="duration" class="block font-medium text-lg text-[#3E3E3E]">Estimated Duration (Mins)</label>
                    <input type="number" name="duration" id="duration" min="5"
                           class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                           value="{{ old('duration', $lesson->duration) }}">
                </div>

                <div>
                    <label for="url" class="block font-medium text-lg text-[#3E3E3E]">Lesson URL (Optional)</label>
                    <input type="url" name="url" id="url"
                           class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                           value="{{ old('url', $lesson->url ?? '') }}" placeholder="https://example.com">
                </div>

                <div class="pt-2">
                    <label class="block font-medium text-lg text-[#3E3E3E]">Current Material</label>
                    @if ($lesson->material_path)
                        <p class="text-[#F26430] text-sm mb-2">Current File: <a href="{{ Storage::url($lesson->material_path) }}" target="_blank" class="hover:underline">View File</a> (Uploading a new file will replace this one.)</p>
                    @else
                        <p class="text-gray-500 text-sm mb-2">No material file currently attached.</p>
                    @endif

                    <label for="material_file" class="block font-medium text-lg text-[#3E3E3E]">Upload New Material (Optional)</label>
                    <input type="file" name="material_file" id="material_file" accept=".pdf, .doc, .docx"
                           class="mt-1 block w-full p-3 border border-gray-400 rounded-md bg-gray-50 cursor-pointer">
                </div>

                <div class="flex items-center justify-start space-x-4 pt-4">
                    <button type="submit" class="bg-[#FFBA08] hover:bg-yellow-600 text-gray-900 font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150">
                        Update Lesson
                    </button>
                    <a href="{{ route('lessons.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
</x-app-layout>