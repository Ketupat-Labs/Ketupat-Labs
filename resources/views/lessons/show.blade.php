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
                        @if(isset($lesson->content_blocks['blocks']) && count($lesson->content_blocks['blocks']) > 0)
                            @foreach($lesson->content_blocks['blocks'] as $index => $block)
                                <div class="lesson-block">
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
                            <a href="{{ Storage::url($lesson->material_path) }}" target="_blank" class="text-[#5FAD56] hover:underline font-bold">
                                {{ basename($lesson->material_path) }}
                            </a>
                        </p>
                    @else
                        <p class="mt-2 text-gray-500">No physical material file available for this lesson.</p>
                    @endif
                </div>


        </div>
    </div>
</div>

{{-- Scroll Progress Tracking Script --}}
<script>
    (function() {
        const lessonId = {{ $lesson->id }};
        const storageKey = `lesson_${lessonId}_progress`;
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');
        
        // Load saved progress
        let maxProgress = parseInt(localStorage.getItem(storageKey) || '0');
        
        function updateProgress(percentage) {
            // Only update if new percentage is higher
            if (percentage > maxProgress) {
                maxProgress = percentage;
                localStorage.setItem(storageKey, maxProgress);
            }
            
            // Update UI
            progressFill.style.width = maxProgress + '%';
            progressText.textContent = maxProgress + '% Complete';
        }
        
        // Initialize with saved progress
        updateProgress(maxProgress);
        
        // Track scroll position
        function handleScroll() {
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Calculate scroll percentage
            const scrollableHeight = documentHeight - windowHeight;
            const scrollPercentage = scrollableHeight > 0 
                ? Math.round((scrollTop / scrollableHeight) * 100)
                : 100;
            
            updateProgress(scrollPercentage);
        }
        
        // Throttle scroll events for performance
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(handleScroll, 100);
        });
        
        // Initial check
        handleScroll();
    })();
</script>
</x-app-layout>