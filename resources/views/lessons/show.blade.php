<x-app-layout>
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-start pb-4 border-b border-gray-200 mb-4">
                <h2 class="text-3xl font-extrabold text-[#2454FF]">
                    {{ $lesson->title }} <span class="text-base text-gray-500">({{ $lesson->topic }})</span>
                </h2>
                <a href="{{ route('lessons.index') }}" class="text-[#5FAD56] hover:text-green-700 font-medium">
                    &larr; Back to Lesson List
                </a>
            </div>

            {{-- PROGRESS TRACKING MOCK (UC004 - Track Progress) --}}
            <div class="progress-bar-area bg-gray-200 h-6 rounded-full mb-6">
                <div class="progress-fill h-full text-center text-white bg-[#5FAD56] rounded-full" style="width: 50%;">
                    50% Complete (Mock Data)
                </div>
            </div>

            <div class="lesson-content-card space-y-6">
                
                <div class="clearfix">
                    <button class="bg-[#F26430] hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg chat-button float-right" onclick="alert('Launching Chatbot Interface (M4)...')">
                        Ask for Help (M4)
                    </button>
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">1. Visual Guide: The Empathy Stage Flow</h3>
                    
                    {{-- VISUAL GUIDE SECTION (Acceptance Criteria: View Visual Aids) --}}
                    <div class="visual-guide border-2 border-[#F26430] p-4 rounded-lg bg-red-50 text-center">
                        <p class="text-gray-600 italic">Placeholder for Syllabus Content Image (HCI 3.1)</p>
                        <img src="https://placehold.co/400x200/F26430/ffffff?text=Empathy+Flowchart" alt="Flowchart of the Empathy Stage" class="mx-auto my-3 border border-gray-400">
                    </div>
                </div>

                <div class="pt-4">
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">2. Lesson Materials</h3>
                    @if ($lesson->material_path)
                        <p class="mt-2 text-lg">Downloadable Material: 
                            <a href="{{ Storage::url($lesson->material_path) }}" target="_blank" class="text-[#5FAD56] hover:underline font-bold">
                                {{ basename($lesson->material_path) }}
                            </a>
                        </p>
                    @else
                        <p class="mt-2 text-gray-500">No physical material file available for this lesson.</p>
                    @endif
                </div>

                <div class="pt-4">
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">3. Assessment Links</h3>
                    <a href="{{ route('lessons.show', ['lesson' => $lesson->id, 'action' => 'quiz']) }}" class="assessment-button text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150" style="background-color: #2454FF; text-decoration: none;">
                        Start Gamified Quiz (UC007)
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>
</x-app-layout>