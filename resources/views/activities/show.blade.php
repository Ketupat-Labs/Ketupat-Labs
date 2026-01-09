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
                            

                            <div class="flex justify-center">
                                {{-- ONLY Simpan Button (Try Again is in the game already) --}}
                                <div id="save-status" class="text-lg font-bold min-h-[3rem] flex items-center justify-center"></div>
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
    <!-- React & ReactDOM (Development Version for better errors) -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    
    <!-- Babel for in-browser JSX compilation -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <script type="text/babel">
        @verbatim
        // Access React/ReactDOM from global scope
        const { useState, useEffect } = React;
        const { createRoot } = ReactDOM;

        // --- Memory Game Component ---
        const MemoryGame = ({ config = {}, onFinish }) => {
            const {
                mode = 'preset',
                theme = 'animals',
                gridSize = 4,
                customPairs = [],
            } = config;

            const themes = {
                animals: ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼'],
                fruits: ['🍎', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍒'],
                emojis: ['😀', '😎', '🤩', '🥳', '😍', '🤗', '😇', '🤠'],
            };

            const [cards, setCards] = useState([]);
            const [flipped, setFlipped] = useState([]);
            const [matched, setMatched] = useState([]);
            const [moves, setMoves] = useState(0);
            const [gameWon, setGameWon] = useState(false);
            const [actualGridSize, setActualGridSize] = useState(4);

            useEffect(() => {
                initializeGame();
            }, [mode, theme, gridSize, customPairs]);

            const initializeGame = () => {
                let cardPairs = [];
                let calculatedGridSize = typeof gridSize === 'string' ? parseInt(gridSize.split('x')[0]) : parseInt(gridSize);
                if (isNaN(calculatedGridSize)) calculatedGridSize = 4;

                if (mode === 'custom' && customPairs && customPairs.length > 0) {
                    const validPairs = customPairs.filter(p => p.card1.trim() && p.card2.trim());
                    if (validPairs.length === 0) {
                        setCards([]);
                        return;
                    }
                    validPairs.forEach((pair, index) => {
                        cardPairs.push({ id: `${index}-a`, content: pair.card1, pairId: index });
                        cardPairs.push({ id: `${index}-b`, content: pair.card2, pairId: index });
                    });
                    calculatedGridSize = Math.ceil(Math.sqrt(cardPairs.length));
                    cardPairs = cardPairs.sort(() => Math.random() - 0.5);
                } else {
                    const icons = themes[theme] || themes.animals;
                    const pairsNeeded = (gridSize * gridSize) / 2;
                    const selectedIcons = icons.slice(0, pairsNeeded);
                    selectedIcons.forEach((icon, index) => {
                        cardPairs.push({ id: `${index}-a`, content: icon, pairId: index });
                        cardPairs.push({ id: `${index}-b`, content: icon, pairId: index });
                    });
                    cardPairs = cardPairs.sort(() => Math.random() - 0.5);
                    calculatedGridSize = gridSize;
                }

                setCards(cardPairs);
                setActualGridSize(calculatedGridSize);
                setFlipped([]);
                setMatched([]);
                setMoves(0);
                setGameWon(false);
            };

            const handleCardClick = (index) => {
                if (flipped.includes(index) || matched.includes(index)) return;
                if (flipped.length === 2) return;

                const newFlipped = [...flipped, index];
                setFlipped(newFlipped);

                if (newFlipped.length === 2) {
                    setMoves(moves + 1);
                    const [first, second] = newFlipped;

                    if (cards[first].pairId === cards[second].pairId) {
                        const newMatched = [...matched, first, second];
                        setMatched(newMatched);
                        setFlipped([]);

                        if (newMatched.length === cards.length) {
                            setGameWon(true);
                            const totalPairs = cards.length / 2;
                            const finalMoves = moves + 1;
                            const optimalWithBuffer = totalPairs + 1;
                            let percentage = 0;
                            if (finalMoves <= optimalWithBuffer) {
                                percentage = 100;
                            } else {
                                percentage = Math.round((optimalWithBuffer / finalMoves) * 100);
                            }

                            if (onFinish) {
                                onFinish({
                                    score: percentage,
                                    moves: finalMoves,
                                    totalPairs: totalPairs,
                                    percentage: percentage,
                                    completionStatus: 'Winner'
                                });
                            }
                        }
                    } else {
                        setTimeout(() => {
                            setFlipped([]);
                        }, 1000);
                    }
                }
            };

            if (cards.length === 0) {
                return (
                    <div className="p-6 bg-red-50 text-center rounded-lg border-2 border-red-200">
                        <p className="text-xl text-red-600">Error: No valid cards configured.</p>
                    </div>
                );
            }

            return (
                <div className="memory-game-container p-6 bg-gradient-to-br from-purple-50 to-blue-50 rounded-lg border-2 border-purple-200">
                    <div className="flex justify-between items-center mb-6">
                        <h3 className="text-2xl font-bold text-purple-700">🎮 Permainan Ingatan</h3>
                        <div className="flex gap-4">
                            <div className="bg-white px-4 py-2 rounded-lg shadow">
                                <span className="text-gray-600 text-sm">Langkah:</span>
                                <span className="ml-2 font-bold text-purple-600">{moves}</span>
                            </div>
                            <button onClick={initializeGame} className="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                                🔄 Mula Semula
                            </button>
                        </div>
                    </div>
                    {gameWon && (
                        <div className="mb-4 p-4 bg-green-100 border-2 border-green-400 rounded-lg text-center">
                            <p className="text-2xl font-bold text-green-700">🎉 Tahniah! Anda menang dalam {moves} langkah! 🎉</p>
                        </div>
                    )}
                    <div className="grid gap-3 mx-auto" style={{
                        gridTemplateColumns: `repeat(${actualGridSize}, minmax(0, 1fr))`,
                        maxWidth: `${actualGridSize * 100}px`,
                    }}>
                        {cards.map((card, index) => {
                            const isFlipped = flipped.includes(index) || matched.includes(index);
                            const isMatched = matched.includes(index);
                            const isCustom = mode === 'custom';
                            return (
                                <div key={card.id} onClick={() => handleCardClick(index)}
                                    className={`aspect-square flex items-center justify-center rounded-lg cursor-pointer transition-all duration-300 transform shadow-lg p-2
                                    ${isFlipped ? 'bg-white' : 'bg-purple-400 hover:bg-purple-500'}
                                    ${isMatched ? 'bg-green-200 border-2 border-green-400' : 'border-2 border-purple-300'}
                                    ${!isFlipped && !isMatched ? 'hover:scale-105' : ''}
                                    ${isCustom ? 'text-sm font-semibold' : 'text-4xl'}`}
                                    style={{ wordWrap: 'break-word', overflow: 'hidden', textAlign: 'center' }}>
                                    {isFlipped ? card.content : '❓'}
                                </div>
                            );
                        })}
                    </div>
                    <div className="mt-6 p-4 bg-white rounded-lg border border-purple-200 text-center">
                        <p className="text-sm text-gray-600">Klik pada kad untuk membalikkannya. Cari pasangan yang sepadan!</p>
                    </div>
                </div>
            );
        };

        // --- Quiz Game Component ---
        const QuizGame = ({ config = {}, onFinish }) => {
            const { questions = [] } = config;
            const [currentQuestion, setCurrentQuestion] = useState(0);
            const [selectedAnswers, setSelectedAnswers] = useState({});
            const [showResults, setShowResults] = useState(false);
            const [score, setScore] = useState(0);

            const validQuestions = questions.filter(q =>
                q.question && q.question.trim() &&
                q.answers && q.answers.filter(a => a && a.trim()).length > 0
            );

            const handleAnswerSelect = (answerIndex) => {
                setSelectedAnswers({
                    ...selectedAnswers,
                    [currentQuestion]: answerIndex
                });
            };

            const handleNext = () => {
                if (currentQuestion < validQuestions.length - 1) {
                    setCurrentQuestion(currentQuestion + 1);
                }
            };

            const handlePrevious = () => {
                if (currentQuestion > 0) {
                    setCurrentQuestion(currentQuestion - 1);
                }
            };

            const handleSubmit = () => {
                let correctCount = 0;
                const breakdown = validQuestions.map((q, index) => {
                    const isCorrect = selectedAnswers[index] === q.correctAnswer;
                    if (isCorrect) correctCount++;
                    return {
                        question: q.question,
                        isCorrect: isCorrect,
                        userAnswer: q.answers[selectedAnswers[index]],
                        correctAnswer: q.answers[q.correctAnswer]
                    };
                });

                setScore(correctCount);
                setShowResults(true);
                const percentage = Math.round((correctCount / validQuestions.length) * 100);

                if (onFinish) {
                    onFinish({
                        score: correctCount,
                        total: validQuestions.length,
                        percentage: percentage,
                        breakdown: breakdown
                    });
                }
            };

            const handleRestart = () => {
                setCurrentQuestion(0);
                setSelectedAnswers({});
                setShowResults(false);
                setScore(0);
            };

            if (validQuestions.length === 0) {
                return (
                    <div className="p-6 bg-blue-50 text-center rounded-lg border-2 border-blue-200">
                        <p className="text-xl text-gray-600">⚠️ No valid questions configured.</p>
                    </div>
                );
            }

            const currentQ = validQuestions[currentQuestion];
            const allAnswered = validQuestions.every((_, index) => selectedAnswers.hasOwnProperty(index));

            return (
                <div className="quiz-game-container p-6 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border-2 border-blue-200">
                    <div className="flex justify-between items-center mb-6">
                        <h3 className="text-2xl font-bold text-blue-700">📝 Quiz Game</h3>
                        <div className="bg-white px-4 py-2 rounded-lg shadow">
                            <span className="text-gray-600 text-sm">Question:</span>
                            <span className="ml-2 font-bold text-blue-600">{currentQuestion + 1} / {validQuestions.length}</span>
                        </div>
                    </div>

                    {!showResults ? (
                        <>
                            <div className="bg-white p-6 rounded-lg shadow-md mb-6">
                                <h4 className="text-xl font-semibold text-gray-800 mb-4">{currentQ.question}</h4>
                                <div className="space-y-3">
                                    {currentQ.answers.map((answer, index) => {
                                        if (!answer.trim()) return null;
                                        const isSelected = selectedAnswers[currentQuestion] === index;
                                        return (
                                            <button key={index} onClick={() => handleAnswerSelect(index)}
                                                className={`w-full text-left p-4 rounded-lg border-2 transition-all duration-200
                                                ${isSelected ? 'border-blue-500 bg-blue-100 shadow-md' : 'border-gray-300 bg-white hover:border-blue-300 hover:bg-blue-50'}`}>
                                                <div className="flex items-center">
                                                    <div className={`w-6 h-6 rounded-full border-2 mr-3 flex items-center justify-center ${isSelected ? 'border-blue-500 bg-blue-500' : 'border-gray-400'}`}>
                                                        {isSelected && <span className="text-white text-xs">✓</span>}
                                                    </div>
                                                    <span className="font-medium text-gray-700">{String.fromCharCode(65 + index)}. {answer}</span>
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                            <div className="flex justify-between items-center">
                                <button onClick={handlePrevious} disabled={currentQuestion === 0}
                                    className={`px-6 py-2 rounded-lg font-semibold transition ${currentQuestion === 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-gray-500 hover:bg-gray-600 text-white'}`}>
                                    ← Previous
                                </button>
                                {currentQuestion === validQuestions.length - 1 ? (
                                    <button onClick={handleSubmit} disabled={!allAnswered}
                                        className={`px-6 py-2 rounded-lg font-semibold transition ${!allAnswered ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 text-white'}`}>
                                        Submit Quiz
                                    </button>
                                ) : (
                                    <button onClick={handleNext} className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                                        Next →
                                    </button>
                                )}
                            </div>
                            <div className="mt-4 flex gap-2 justify-center">
                                {validQuestions.map((_, index) => (
                                    <div key={index} className={`w-3 h-3 rounded-full transition-all ${index === currentQuestion ? 'bg-blue-600 scale-125' : ''} ${selectedAnswers.hasOwnProperty(index) ? 'bg-blue-400' : 'bg-gray-300'}`} />
                                ))}
                            </div>
                        </>
                    ) : (
                        <div className="text-center py-12">
                            <div className="bg-gradient-to-br from-green-50 to-blue-50 p-8 rounded-2xl shadow-lg mb-6 border-2 border-green-200">
                                <h3 className="text-3xl font-bold text-gray-800 mb-4">🎉 Quiz Complete!</h3>
                                <div className="text-6xl font-bold text-green-600 mb-2">{score} / {validQuestions.length}</div>
                                <p className="text-xl text-gray-700 mb-4">{score === validQuestions.length ? 'Perfect Score! 🌟' : 'Great Job! 👏'}</p>
                                <p className="text-sm text-gray-600">View detailed results in <strong>Lihat Prestasi</strong> dashboard</p>
                            </div>
                            <button onClick={handleRestart} className="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-full font-bold transition text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                🔄 Try Again
                            </button>
                        </div>
                    )}
                </div>
            );
        };

        // --- Init Logic ---
        const gameComponents = {
            'memory': MemoryGame,
            'quiz': QuizGame,
        };

        window.initializeGames = function () {
            const gameContainers = document.querySelectorAll('[data-game-block]');
            console.log('Game loader: Found', gameContainers.length, 'game containers');

            gameContainers.forEach(container => {
                const gameType = container.dataset.gameType;
                const blockId = container.dataset.id;
                let gameConfig = {};

                try {
                    gameConfig = JSON.parse(container.dataset.gameConfig || '{}');
                } catch (e) {
                    console.error('Failed to parse game config:', e);
                    return;
                }

                console.log('Loading game:', gameType, gameConfig);

                const GameComponent = gameComponents[gameType];
                if (GameComponent) {
                    try {
                        let root;
                        if (container._reactRoot) {
                            console.log('Game root already exists, re-rendering...');
                            root = container._reactRoot;
                        } else {
                            root = createRoot(container);
                            container._reactRoot = root;
                        }
                        
                        root.render(
                            <GameComponent
                                config={gameConfig}
                                onFinish={(results) => {
                                    if (window.handleGameScore) {
                                        window.handleGameScore(results, blockId);
                                    }
                                }}
                            />
                        );
                        console.log('✓ Game loaded successfully:', gameType);
                    } catch (err) {
                        console.error('Error rendering game:', err);
                    }
                } else {
                    console.warn('✗ Unknown game type:', gameType);
                }
            });
        };


        @endverbatim
    </script>

    <script>
        // --- Score Handling Logic (Vanilla JS) ---
        let latestResults = null;

        // Start Button Logic
        function attachStartListener() {
            const startBtn = document.getElementById('start-btn');
            if(startBtn) {
                startBtn.addEventListener('click', function() {
                    const startArea = document.getElementById('start-area');
                    const gameArea = document.getElementById('game-area');
                    
                    if(startArea) startArea.classList.add('hidden');
                    if(gameArea) {
                        gameArea.classList.remove('hidden');
                        setTimeout(() => {
                            gameArea.classList.add('opacity-100');
                        }, 50);
                        
                        if (window.initializeGames) {
                            window.initializeGames();
                        } else {
                             console.log("Waiting for game script...");
                             setTimeout(() => {
                                 if (window.initializeGames) window.initializeGames();
                             }, 500);
                        }
                        window.dispatchEvent(new Event('resize'));
                    }
                });
            }
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', attachStartListener);
        } else {
            attachStartListener();
        }

        window.handleGameScore = function(results) {
            console.log("Game Finished. Results:", results);
            latestResults = results;
            const finalScore = results.percentage || 0;
            
            const completionArea = document.getElementById('completion-area');
            const tempScoreDisplay = document.getElementById('temp-score-display');
            const statusDiv = document.getElementById('save-status');
            
            if(completionArea) {
                completionArea.classList.remove('hidden');
                setTimeout(() => {
                    completionArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);

                if(tempScoreDisplay) tempScoreDisplay.textContent = finalScore + '%';
                
                // Auto Save Logic
                if (statusDiv) {
                    statusDiv.innerHTML = '<span class="text-blue-600 flex items-center gap-2"><svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sedang Menyimpan Keputusan...</span>';
                }

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
                        if(statusDiv) {
                            statusDiv.innerHTML = '<span class="text-green-600 font-bold flex items-center gap-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Keputusan Disimpan Secara Automatik!</span>';
                        }
                    } else {
                        if(statusDiv) statusDiv.innerHTML = '<span class="text-red-500">⚠ Ralat menyimpan: ' + (data.error || 'Unknown') + '</span>';
                    }
                })
                .catch(error => {
                    console.error('Network error:', error);
                    if(statusDiv) statusDiv.innerHTML = '<span class="text-red-500">⚠ Ralat rangkaian semasa menyimpan.</span>';
                });
            }
        };
    </script>
    @endpush
</x-app-layout>
