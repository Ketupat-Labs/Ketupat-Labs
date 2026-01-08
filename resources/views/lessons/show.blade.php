<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $lesson->title }} <span class="text-sm text-gray-500 font-normal">({{ $lesson->topic }})</span>
            </h2>
            <a href="{{ route('lessons.index') }}" class="text-[#5FAD56] hover:text-green-700 font-medium flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Senarai Pelajaran
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

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
                                    
                                    @elseif($block['type'] === 'game' || $block['type'] === 'memory')
                                        <div class="game-container my-6">
                                            @php
                                                $gameConfig = (is_array($block['content'])) ? $block['content'] : (json_decode($block['content'], true) ?? ['theme' => 'animals', 'gridSize' => 4]);
                                            @endphp
                                            <div 
                                                data-game-block 
                                                data-game-type="memory"
                                                data-id="{{ $block['id'] ?? $index }}"
                                                data-game-config='@json($gameConfig)'>
                                            </div>
                                        </div>
                                    
                                    @elseif($block['type'] === 'quiz')
                                        <div class="quiz-container my-6">
                                            @php
                                                $quizConfig = (is_array($block['content'])) ? $block['content'] : (json_decode($block['content'], true) ?? ['questions' => []]);
                                            @endphp
                                            <div 
                                                data-game-block 
                                                data-game-type="quiz"
                                                data-id="{{ $block['id'] ?? $index }}"
                                                data-game-config='@json($quizConfig)'>
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
                            <a href="{{ Storage::url($lesson->material_path) }}" target="_blank" class="text-[#5FAD56] hover:underline font-bold">
                                {{ basename($lesson->material_path) }}
                            </a>
                        </p>
                    @else
                        <p class="mt-2 text-gray-500">Tiada fail bahan fizikal tersedia untuk pelajaran ini.</p>
                    @endif
                </div>


        </div>
    </div>
</div>
</div>
</x-app-layout>