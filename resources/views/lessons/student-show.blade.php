<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-start pb-4 border-b border-gray-200 mb-4">
                    <h2 class="text-3xl font-extrabold text-[#2454FF]">
                        {{ $lesson->title }} <span class="text-base text-gray-500">({{ $lesson->topic }})</span>
                    </h2>
                    <a href="{{ route('lesson.index') }}" class="text-[#5FAD56] hover:text-green-700 font-medium">
                        &larr; Kembali ke Pelajaran
                    </a>
                </div>

                {{-- PROGRESS TRACKING --}}
                <div class="progress-bar-area bg-gray-200 h-6 rounded-full mb-6">
                    <div id="progress-fill" class="progress-fill h-full text-center text-white bg-[#5FAD56] rounded-full transition-all duration-300"
                        style="width: 0%;">
                        <span id="progress-text">0% Selesai</span>
                    </div>
                </div>

                <div class="lesson-content-card space-y-6">

                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">Kandungan Pelajaran</h3>
                        
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
                                                    <h4 class="text-lg font-semibold text-gray-800 mb-3">📹 Demonstrasi Video</h4>
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
                                                    <p class="text-sm text-gray-600 mt-2">Klik imej untuk tonton di YouTube</p>
                                                </div>
                                            @endif
                                        
                                        @elseif($block['type'] === 'image')
                                            <div class="image-container my-6">
                                                <h4 class="text-lg font-semibold text-gray-800 mb-3">🖼️ Panduan Visual</h4>
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
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">Bahan Pelajaran</h3>
                        @if ($lesson->material_path)
                            <p class="mt-2 text-lg">Bahan Boleh Muat Turun:
                                <a href="{{ Storage::url($lesson->material_path) }}" target="_blank"
                                    class="text-[#5FAD56] hover:underline font-bold">
                                    {{ basename($lesson->material_path) }}
                                </a>
                            </p>
                        @else
                            <p class="mt-2 text-gray-500">Tiada fail bahan fizikal tersedia untuk pelajaran ini.</p>
                        @endif
                    </div>

                    <div class="pt-4">
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">Penghantaran Latihan Amali
                        </h3>
                        <p class="mb-4 text-gray-700 mt-2">Muat naik fail latihan amali anda di sini untuk dinilai.</p>

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
                                <strong class="font-bold">Aduh!</strong>
                                <span class="block sm:inline">Terdapat beberapa masalah dengan input anda.</span>
                                <ul class="mt-2 list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(isset($submission))
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <h4 class="font-bold text-lg mb-2">Status Penghantaran</h4>
                                <p><strong>Status:</strong>
                                    <span
                                        class="@if($submission->status == 'Graded') text-green-600 @else text-orange-600 @endif font-bold">
                                        {{ $submission->status == 'Graded' ? 'Dinilai' : 'Belum Dinilai' }}
                                    </span>
                                </p>
                                <p><strong>Fail:</strong> {{ $submission->file_name }}</p>
                                <p><strong>Dihantar:</strong> {{ $submission->created_at->format('d M Y, H:i') }}</p>

                                @if($submission->status == 'Graded')
                                    <div class="mt-3 pt-3 border-t border-blue-200">
                                        <p class="text-lg"><strong>Gred:</strong> {{ $submission->grade }}/100</p>
                                        @if($submission->feedback)
                                            <p class="mt-1"><strong>Maklum Balas:</strong> {{ $submission->feedback }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if($submission->status !== 'Graded')
                                <p class="text-sm text-gray-500 mb-2">Anda boleh muat naik semula untuk mengemaskini penghantaran anda sebelum
                                    dinilai.</p>
                            @endif
                        @endif

                        @if(!isset($submission) || $submission->status !== 'Graded')
                            <form action="{{ route('submission.submit') }}" method="POST" enctype="multipart/form-data"
                                class="mt-4">
                                @csrf
                                <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">

                                <div class="mb-4">
                                    <label for="submission_file" class="block text-gray-700 text-sm font-bold mb-2">Muat Naik
                                        Fail (Pilihan):</label>
                                    <p class="text-xs text-gray-500 mb-2">Jika pelajaran ini tidak memerlukan fail, anda hanya perlu klik butang di bawah untuk menandakannya sebagai selesai.</p>
                                    <input type="file" name="submission_file" id="submission_file"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    @error('submission_file')
                                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                    class="bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    {{ isset($submission) ? 'Kemaskini Penghantaran' : 'Tandakan Pelajaran sebagai Selesai / Hantar Tugasan' }}
                                </button>
                            </form>
                        @endif
                    </div>



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
                progressText.textContent = maxProgress + '% Selesai';
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