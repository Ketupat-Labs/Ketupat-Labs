<x-app-layout>
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-start pb-4 border-b border-gray-200 mb-4">
                <h2 class="text-3xl font-extrabold text-[#2454FF]">
                    {{ $lesson->title }} <span class="text-base text-gray-500">({{ $lesson->topic }})</span>
                </h2>
                <a href="{{ route('lesson.index') }}" class="text-[#5FAD56] hover:text-green-700 font-medium">
                    &larr; Back to Lessons
                </a>
            </div>

            {{-- PROGRESS TRACKING --}}
            <div class="progress-bar-area bg-gray-200 h-6 rounded-full mb-6">
                <div class="progress-fill h-full text-center text-white bg-[#5FAD56] rounded-full" style="width: 50%;">
                    50% Complete
                </div>
            </div>

            <div class="lesson-content-card space-y-6">
                
                <div class="clearfix">
                    <button class="bg-[#F26430] hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg chat-button float-right" onclick="alert('Launching Chatbot Interface (M4)...')">
                        Ask for Help (M4)
                    </button>
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">1. Theory: What is Interaction Design?</h3>
                    <p class="mt-3 text-gray-700">Interaction design focuses on achieving usability by understanding <strong>human needs</strong> and re-framing problems in <strong>human-centric ways</strong>.</p>
                </div>

                <div class="pt-4">
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">2. Embedded Video Demonstration</h3>
                    <div class="text-center my-6">
                        <a href="https://youtu.be/AawJ9IIdJtk?si=kle7vJJBaZKhvYbB" target="_blank" class="inline-block">
                            <img src="https://placehold.co/500x300/F26430/ffffff?text=Click+to+Watch+Video" 
                                alt="Video Placeholder: Click to watch externally" 
                                class="border-4 border-[#F26430] cursor-pointer rounded-lg hover:opacity-90 transition-opacity">
                        </a>
                        <p class="text-sm text-gray-600 mt-2">Click image to view on YouTube</p>
                    </div>
                </div>
                
                <div class="pt-4">
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">3. Visual Guide: Empathy Stage Flow</h3>
                    <div class="visual-guide border-2 border-[#F26430] p-4 rounded-lg bg-red-50 mt-4">
                        <img src="https://placehold.co/600x400/F26430/ffffff?text=Empathy+Flowchart" 
                            alt="Flowchart of the Empathy Stage" 
                            class="w-full h-auto border border-gray-400 rounded mb-3">
                        <p class="text-gray-700">This visual guide breaks down the abstract concept of the <strong>Empathise stage</strong>, showing how designers gather user data before defining the problem.</p>
                    </div>
                </div>

                <div class="pt-4">
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">4. Lesson Materials</h3>
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
                    <p class="mb-4 text-gray-700">Once you reach 100% completion, you can proceed to the assessment.</p>
                    <a href="{{ route('quiz.show', $lesson->id) }}" 
                       class="inline-block bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150">
                        Go to Gamified Quiz (UC007)
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>
</x-app-layout>

