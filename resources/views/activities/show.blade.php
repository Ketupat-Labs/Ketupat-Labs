<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Butiran Aktiviti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <a href="{{ url()->previous() }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
                    </div>

                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $activity->title }}</h1>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">{{ $activity->type }}</span>
                        <span class="text-gray-600 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $activity->suggested_duration }}
                        </span>
                    </div>

                    <div class="prose max-w-none mb-8">
                        <h3 class="text-lg font-semibold mb-2">Penerangan</h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ $activity->description }}</p>
                    </div>



                    {{-- Game/Quiz Rendering --}}
                    @php
                        $rawContent = $activity->content;
                        $config = json_decode($rawContent, true) ?? [];
                        
                        // Strict validation based on type
                        $isValid = false;
                        if ($activity->type === 'Game') {
                            // Valid if it has custom pairs OR is in preset mode
                            if (!empty($config['customPairs']) || ($config['mode'] ?? '') === 'preset') {
                                $isValid = true;
                            }
                        } elseif ($activity->type === 'Quiz' && !empty($config['questions'])) {
                            $isValid = true;
                        }
                        
                        $gameType = strtolower($activity->type) === 'quiz' ? 'quiz' : 'memory'; 
                    @endphp

                    @if($isValid)
                        {{-- Start Button (Visible Initially) --}}
                        <div id="start-area" class="my-6 text-center py-6 bg-gray-50 rounded-lg border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-800 mb-3">Aktiviti sedia untuk dimulakan</h3>
                            <button id="start-btn" class="bg-blue-600 text-white px-6 py-2 rounded-full text-lg font-bold hover:bg-blue-700 transition shadow hover:shadow-lg transform hover:-translate-y-1 flex items-center justify-center gap-2 mx-auto">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Mula Aktiviti
                            </button>
                        </div>

                        {{-- Game Area (Hidden Initially) --}}
                        <div class="activity-content-area border-t pt-6 hidden transition-all duration-500 ease-in-out" id="game-area">
                            <div class="game-container my-6">
                                {{-- Game Header --}}
                                <h3 class="text-xl font-bold mb-4 text-center text-indigo-700">
                                    {{ $gameType === 'quiz' ? 'Jawab Kuiz' : 'Main Permainan' }}
                                </h3>
                                
                                <div 
                                    class="w-full"
                                    data-game-block 
                                    data-game-type="{{ $gameType }}"
                                    data-game-config='@json($config)'>
                                </div>
                            </div>
                        </div>

                        {{-- Completion Area (Renamed & Moved Here) --}}
                        <div id="completion-area" class="hidden my-8 p-8 bg-green-50 border-2 border-green-400 rounded-xl text-center shadow-xl relative z-50">
                            <h3 class="text-2xl font-extrabold text-green-800 mb-3">🎉 Tahniah! Aktiviti Selesai.</h3>
                            <p class="text-gray-700 mb-6 text-lg">Markah Terkumpul Anda: <span id="temp-score-display" class="font-bold text-2xl text-green-600">0%</span></p>
                            
                            <div class="flex justify-center">
                                {{-- ONLY Simpan Button (Try Again is in the game already) --}}
                                <button id="submit-finish-btn" class="px-8 py-3 bg-green-600 text-white rounded-full font-bold text-lg hover:bg-green-700 transition shadow-2xl transform hover:-translate-y-1 flex items-center ring-4 ring-green-200">
                                    Simpan Perkembangan 
                                    <svg class="w-6 h-6 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- ... (Invalid Content Validation UI) ... --}}
                        <div class="my-8 text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                             <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900">Perlukan Kandungan Permainan</h3>
                            <p class="text-gray-500 mt-2">Aktiviti ini tidak mempunyai konfigurasi permainan yang lengkap.</p>
                            
                            @php
                                $userRole = auth()->user()->role ?? '';
                            @endphp
                            @if($userRole === 'teacher')
                                <div class="mt-4">
                                    <a href="{{ route('activities.edit', $activity->id) }}" class="text-blue-600 hover:underline font-bold">
                                        &rarr; Klik di sini untuk menambah kandungan (Soalan/Kad)
                                    </a>
                                </div>
                            @else
                                <p class="text-gray-500 mt-2">Sila hubungi guru anda.</p>
                            @endif
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startBtn = document.getElementById('start-btn');
            if(startBtn) {
                startBtn.addEventListener('click', function() {
                    const startArea = document.getElementById('start-area');
                    const gameArea = document.getElementById('game-area');
                    
                    if(startArea) startArea.classList.add('hidden');
                        if(gameArea) {
                            gameArea.classList.remove('hidden');
                            // Small delay to ensure transition starts
                            setTimeout(() => {
                                gameArea.classList.add('opacity-100');
                            }, 50);
                            
                            // Safe to call multiple times now
                            if (window.initializeGames) {
                                window.initializeGames();
                            }
                            
                            // Force resize event for any layout calculations
                            window.dispatchEvent(new Event('resize'));
                        }
                });
            }
        });

        let latestResults = null;

        window.handleGameScore = function(results) {
            console.log("Game Finished. Results:", results);
            latestResults = results;
            const finalScore = results.percentage || 0;
            
            // Show Completion Area
            const gameArea = document.getElementById('game-area');
            const completionArea = document.getElementById('completion-area');
            const tempScoreDisplay = document.getElementById('temp-score-display');
            const submitBtn = document.getElementById('submit-finish-btn');
            
            if(completionArea) {
                if (!completionArea.classList.contains('hidden')) {
                     return; // Already shown
                }
                
                completionArea.classList.remove('hidden');
                // Scroll to completion area
                setTimeout(() => {
                    completionArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);

                if(tempScoreDisplay) tempScoreDisplay.textContent = finalScore + '%';
                
                // Handle Submit Button Click
                submitBtn.onclick = function() {
                    // ... (keep existing logic)
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sedang Simpan...';
                    
                    fetch("{{ route('activities.submit', $activity->id) }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ 
                            score: finalScore,
                            results: latestResults
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Kemajuan Disimpan! Tahniah.');
                             window.location.href = "{{ route('lessons.index') }}?tab=activities"; 
                        } else {
                            alert('Ralat: ' + (data.error || 'Unknown error'));
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Simpan Perkembangan <i class="fas fa-save ml-2"></i>';
                        }
                    })
                    .catch(error => {
                        console.error('Network error:', error);
                        alert('Ralat rangkaian. Sila cuba lagi.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Simpan Perkembangan <i class="fas fa-save ml-2"></i>';
                    });
                };
            }
        };

        // Fallback: Watch for "Quiz Complete!" or "Game Over" in the DOM
        const gameObserver = new MutationObserver(function(mutations) {
            const gameArea = document.getElementById('game-area');
            if (!gameArea) return;

            // Check if we found the completion text
            const quizCompleteHeader = Array.from(gameArea.querySelectorAll('h3')).find(el => el.textContent.includes('Quiz Complete') || el.textContent.includes('Game Over') || el.textContent.includes('Tahniah'));
            
            if (quizCompleteHeader) {
                // Try to find score
                // QuizGame structure: <div class="text-6xl ..."> 5 / 10 </div>
                // MemoryGame structure: might be different.
                
                // Try to parse score from nearby elements
                let score = 0;
                let total = 0;
                let percentage = 0;
                
                const scoreDiv = quizCompleteHeader.parentElement.querySelector('.text-6xl');
                if (scoreDiv && scoreDiv.textContent.includes('/')) {
                     const parts = scoreDiv.textContent.split('/');
                     score = parseInt(parts[0].trim());
                     total = parseInt(parts[1].trim());
                     percentage = total > 0 ? Math.round((score / total) * 100) : 0;
                } else {
                    // Fallback for Memory Game or others: Look for "Score:" or numbers
                    // Assume 100% if we can't parse, or default to 0 and let user edit? Better to default to 100 if it's "Completed" successfully without score?
                    // Actually, Memory Game usually completes with full success.
                    // Let's stick to 100 if we see "Complete" but no score.
                    percentage = 100;
                }
                
                console.log("Observer detected completion. Score Parsed:", percentage);
                window.handleGameScore({ percentage: percentage });
                
                gameObserver.disconnect(); // Stop watching once triggered
            }
        });

        const gameContainer = document.getElementById('game-area');
        if (gameContainer) {
            gameObserver.observe(gameContainer, { childList: true, subtree: true, characterData: true });
        }
    </script>
    @endpush
</x-app-layout>
