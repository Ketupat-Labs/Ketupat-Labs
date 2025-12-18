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
                    <div id="progress-fill" class="progress-fill h-full text-center text-white bg-[#5FAD56] rounded-full transition-all duration-300"
                        style="width: 0%;">
                        <span id="progress-text">0% Complete</span>
                    </div>
                </div>

                <div class="lesson-content-card space-y-6">

                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">Lesson Content</h3>
                        
                        {{-- Dynamic Block Rendering --}}
                        <div class="mt-6 space-y-6">
                            @php
                                $completedItems = $enrollment ? (json_decode($enrollment->completed_items, true) ?? []) : [];
                            @endphp

                            @if(isset($lesson->content_blocks['blocks']) && count($lesson->content_blocks['blocks']) > 0)
                                @foreach($lesson->content_blocks['blocks'] as $index => $block)
                                    @php
                                        $blockId = $block['id'] ?? 'block_' . $index;
                                        $isCompleted = in_array($blockId, $completedItems);
                                        $totalItems = count($lesson->content_blocks['blocks']);
                                    @endphp
                                    <div class="lesson-block group relative" id="block-container-{{ $blockId }}">
                                        
                                        {{-- Completion Controls --}}
                                        <div class="absolute right-0 top-0 opacity-100 transition-opacity duration-200 print:hidden">
                                            @if($enrollment)
                                                <button 
                                                    onclick="toggleItemCompletion('{{ $blockId }}')"
                                                    class="flex items-center space-x-2 px-3 py-1 rounded-full text-sm font-medium transition-colors border {{ $isCompleted ? 'bg-green-100 text-green-700 border-green-300' : 'bg-gray-100 text-gray-500 border-gray-300 hover:bg-blue-50 hover:text-blue-600' }}"
                                                    id="btn-{{ $blockId }}">
                                                    <span id="icon-{{ $blockId }}">{{ $isCompleted ? '✓' : '○' }}</span>
                                                    <span id="text-{{ $blockId }}">
                                                        @if($block['type'] === 'youtube') Mark as Viewed
                                                        @elseif($block['type'] === 'game' || $block['type'] === 'quiz') Mark as Done
                                                        @else Mark as Read @endif
                                                    </span>
                                                </button>
                                            @endif
                                        </div>

                                        @if($block['type'] === 'heading')
                                            <h2 class="text-2xl font-bold text-gray-900 mb-3">
                                                {{ $block['content'] }}
                                            </h2>
                                        
                                        @elseif($block['type'] === 'text')
                                            <div class="text-gray-700 prose max-w-none leading-relaxed">
                                                {!! nl2br(e($block['content'])) !!}
                                            </div>
                                        
                                        @elseif($block['type'] === 'youtube')
                                            @php
                                                // Extract YouTube video ID from URL
                                                $videoUrl = $block['content'];
                                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $matches);
                                                $videoId = $matches[1] ?? null;
                                            @endphp
                                            
                                            @if($videoId)
                                                <div class="video-container my-6">
                                                    <h4 class="text-lg font-semibold text-gray-800 mb-3">📹 Video Demonstration</h4>
                                                    <div class="relative" style="padding-bottom: 56.25%; height: 0;">
                                                        <iframe 
                                                            src="https://www.youtube.com/embed/{{ $videoId }}" 
                                                            frameborder="0" 
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                            allowfullscreen
                                                            class="absolute top-0 left-0 w-full h-full rounded-lg border-4 border-[#F26430]">
                                                        </iframe>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center my-6">
                                                    <a href="{{ $block['content'] }}" target="_blank" class="inline-block">
                                                        <img src="https://placehold.co/500x300/F26430/ffffff?text=Click+to+Watch+Video" 
                                                             alt="Video Placeholder" 
                                                             class="border-4 border-[#F26430] cursor-pointer rounded-lg hover:opacity-90 transition-opacity">
                                                    </a>
                                                    <p class="text-sm text-gray-600 mt-2">Click image to view on YouTube</p>
                                                </div>
                                            @endif
                                        
                                        @elseif($block['type'] === 'image')
                                            <div class="image-container my-6">
                                                <h4 class="text-lg font-semibold text-gray-800 mb-3">🖼️ Visual Guide</h4>
                                                <div class="border-2 border-[#F26430] p-4 rounded-lg bg-red-50">
                                                    <img src="{{ $block['content'] }}" 
                                                         alt="Lesson Image" 
                                                         class="w-full h-auto border border-gray-400 rounded mb-3">
                                                </div>
                                            </div>
                                        
                                        @elseif($block['type'] === 'game')
                                            <div class="game-container my-6">
                                                @php
                                                    $gameConfig = json_decode($block['content'], true) ?? ['theme' => 'animals', 'gridSize' => 4];
                                                @endphp
                                                <div 
                                                    data-game-block 
                                                    data-game-type="memory"
                                                    data-block-id="{{ $blockId }}"
                                                    data-game-config="{{ json_encode($gameConfig) }}">
                                                </div>
                                            </div>
                                        
                                        @elseif($block['type'] === 'quiz')
                                            <div class="quiz-container my-6">
                                                @php
                                                    $quizConfig = json_decode($block['content'], true) ?? ['questions' => []];
                                                @endphp
                                                <div 
                                                    data-game-block 
                                                    data-game-type="quiz"
                                                    data-block-id="{{ $blockId }}"
                                                    data-game-config="{{ json_encode($quizConfig) }}">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                {{-- Fallback to old content field if no blocks --}}
                                <div class="text-gray-700 prose max-w-none">
                                    {!! nl2br(e($lesson->content)) !!}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="pt-4">
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">Lesson Materials</h3>
                        @if ($lesson->material_path)
                            <p class="mt-2 text-lg">Downloadable Material:
                                <a href="{{ Storage::url($lesson->material_path) }}" target="_blank"
                                    class="text-[#5FAD56] hover:underline font-bold">
                                    {{ basename($lesson->material_path) }}
                                </a>
                            </p>
                        @else
                            <p class="mt-2 text-gray-500">No physical material file available for this lesson.</p>
                        @endif
                    </div>

                    <div class="pt-4">
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">Practical Exercise Submission
                        </h3>
                        <p class="mb-4 text-gray-700 mt-2">Upload your practical exercise file here for grading.</p>

                        @if(session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <strong class="font-bold">Whoops!</strong>
                                <span class="block sm:inline">There were some problems with your input.</span>
                                <ul class="mt-2 list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(isset($submission))
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <h4 class="font-bold text-lg mb-2">Submission Status</h4>
                                <p><strong>Status:</strong>
                                    <span
                                        class="@if($submission->status == 'Graded') text-green-600 @else text-orange-600 @endif font-bold">
                                        {{ $submission->status }}
                                    </span>
                                </p>
                                <p><strong>File:</strong> {{ $submission->file_name }}</p>
                                <p><strong>Submitted:</strong> {{ $submission->created_at->format('d M Y, H:i') }}</p>

                                @if($submission->status == 'Graded')
                                    <div class="mt-3 pt-3 border-t border-blue-200">
                                        <p class="text-lg"><strong>Grade:</strong> {{ $submission->grade }}/100</p>
                                        @if($submission->feedback)
                                            <p class="mt-1"><strong>Feedback:</strong> {{ $submission->feedback }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if($submission->status !== 'Graded')
                                <p class="text-sm text-gray-500 mb-2">You can re-upload to update your submission before
                                    grading.</p>
                            @endif
                        @endif

                        @if(!isset($submission) || $submission->status !== 'Graded')
                            <form action="{{ route('submission.submit') }}" method="POST" enctype="multipart/form-data"
                                class="mt-4">
                                @csrf
                                <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">

                                <div class="mb-4">
                                    <label for="submission_file" class="block text-gray-700 text-sm font-bold mb-2">Upload
                                        File (Optional):</label>
                                    <p class="text-xs text-gray-500 mb-2">If this lesson does not require a file, you can just click the button below to mark it as complete.</p>
                                    <input type="file" name="submission_file" id="submission_file"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    @error('submission_file')
                                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                    class="bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    {{ isset($submission) ? 'Update Submission' : 'Mark Lesson as Complete / Submit Assignment' }}
                                </button>
                            </form>
                        @endif
                    </div>



                </div>

            </div>
        </div>
    </div>

    {{-- Scroll Progress Tracking Script --}}
    {{-- Progress Tracking Script --}}
    <script>
        const enrollmentId = "{{ $enrollment ? $enrollment->id : '' }}";
        const csrfToken = "{{ csrf_token() }}";
        const totalItems = {{ (isset($lesson->content_blocks['blocks']) ? count($lesson->content_blocks['blocks']) : 0) + 1 }};
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');

        // Init Progress UI
        const currentProgress = {{ $enrollment ? $enrollment->progress : 0 }};
        console.log('Initial Progress:', currentProgress);
        progressFill.style.width = currentProgress + '%';
        progressText.textContent = currentProgress + '% Complete';

        // Global function for React components to report completion
        window.reportItemCompletion = function(itemId) {
            console.log('Global reportItemCompletion called for:', itemId);
            const btn = document.getElementById(`btn-${itemId}`);
            if (btn) {
                // Check if already completed
                if (!btn.classList.contains('bg-green-100')) {
                    toggleItemCompletion(itemId);
                } else {
                    console.log('Item already completed:', itemId);
                }
            } else {
                console.error('Completion button not found for:', itemId);
            }
        };

        function toggleItemCompletion(itemId) {
            console.log('Toggle Clicked:', itemId);

            if (!enrollmentId) {
                alert('Please enroll in this lesson to track progress.');
                return;
            }

            const btn = document.getElementById(`btn-${itemId}`);
            
            // Determine current state based on button class (completed has bg-green-100)
            const isCompleted = btn.classList.contains('bg-green-100');
            const newStatus = isCompleted ? 'incomplete' : 'completed';
            console.log('Current State:', isCompleted, 'New Status:', newStatus);

            // Optimistic UI Update
            updateButtonState(itemId, !isCompleted);

            fetch(`/enrollment/${enrollmentId}/progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    item_id: itemId,
                    status: newStatus,
                    total_items: totalItems
                })
            })
            .then(response => {
                console.log('Response Status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                console.log('Server Data:', data);
                if (data.success) {
                    // Update Progress Bar
                    console.log('Updating Progress To:', data.progress);
                    progressFill.style.width = data.progress + '%';
                    progressText.textContent = data.progress + '% Complete';
                } else {
                    // Revert if error
                    console.error('Server reported error:', data.message);
                    updateButtonState(itemId, isCompleted);
                    alert('Error updating progress: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                updateButtonState(itemId, isCompleted);
                alert('Connection error. Please check console for details.');
            });
        }

        function updateButtonState(itemId, completed) {
            const btn = document.getElementById(`btn-${itemId}`);
            const icon = document.getElementById(`icon-${itemId}`);
            const container = document.getElementById(`block-container-${itemId}`);

            if (!btn) { 
                console.error('Button not found for:', itemId); 
                return; 
            }

            if (completed) {
                btn.classList.remove('bg-gray-100', 'text-gray-500', 'border-gray-300', 'hover:bg-blue-50', 'hover:text-blue-600');
                btn.classList.add('bg-green-100', 'text-green-700', 'border-green-300');
                icon.textContent = '✓';
            } else {
                btn.classList.add('bg-gray-100', 'text-gray-500', 'border-gray-300', 'hover:bg-blue-50', 'hover:text-blue-600');
                btn.classList.remove('bg-green-100', 'text-green-700', 'border-green-300');
                icon.textContent = '○';
            }
        }
    </script>
</x-app-layout>