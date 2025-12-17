<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Aktiviti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('activities.update', $activity->id) }}" method="POST" onsubmit="return prepareSubmission()">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Tajuk Aktiviti</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $activity->title) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        </div>

                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700">Jenis Aktiviti</label>
                            <select name="type" id="type" onchange="toggleGameConfig()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="Game" {{ $activity->type === 'Game' ? 'selected' : '' }}>Memory Game</option>
                                <option value="Quiz" {{ $activity->type === 'Quiz' ? 'selected' : '' }}>Quiz Game</option>
                                <option value="Exercise" {{ $activity->type === 'Exercise' ? 'selected' : '' }}>Latihan</option>
                                <option value="Video" {{ $activity->type === 'Video' ? 'selected' : '' }}>Video</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="suggested_duration" class="block text-sm font-medium text-gray-700">Cadangan Masa</label>
                            <input type="text" name="suggested_duration" id="suggested_duration" value="{{ old('suggested_duration', $activity->suggested_duration) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Penerangan (Pilihan)</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $activity->description) }}</textarea>
                        </div>

                        <!-- Hidden JSON Input -->
                        <textarea name="content" id="content" class="hidden">{{ old('content', $activity->content) }}</textarea>

                        <!-- Visual Game Builder -->
                        <div id="game-builder-area" class="mb-6 bg-gray-50 p-6 rounded-lg border-2 border-dashed border-gray-300 hidden">
                            <h3 class="text-lg font-bold text-gray-800 mb-4" id="builder-title">Game Configuration</h3>
                            
                            <!-- Memory Game Builder -->
                            <div id="memory-builder" class="hidden">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Matching Pairs</label>
                                    <div id="memory-pairs-container" class="space-y-3">
                                        <!-- Pairs will be added here -->
                                    </div>
                                    <button type="button" onclick="addMemoryPair()" class="mt-3 text-sm flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                                        <span class="text-xl mr-1">+</span> Add New Pair
                                    </button>
                                </div>
                            </div>

                            <!-- Quiz Game Builder -->
                            <div id="quiz-builder" class="hidden">
                                <div id="questions-container" class="space-y-6">
                                    <!-- Questions will be added here -->
                                </div>
                                <button type="button" onclick="addQuizQuestion()" class="mt-4 w-full py-3 border-2 border-dashed border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 font-semibold transition">
                                    + Add New Question
                                </button>
                            </div>
                        </div>

                        <script>
                            // Initial State from Server
                            let memoryPairs = [];
                            let quizQuestions = [];
                            
                            try {
                                const initialContent = JSON.parse(document.getElementById('content').value || '{}');
                                if (initialContent.customPairs) {
                                    memoryPairs = initialContent.customPairs;
                                }
                                if (initialContent.questions) {
                                    quizQuestions = initialContent.questions;
                                }
                            } catch (e) {
                                console.error("Failed to parse existing content", e);
                            }

                            function toggleGameConfig() {
                                const type = document.getElementById('type').value;
                                const builderArea = document.getElementById('game-builder-area');
                                const memoryBuilder = document.getElementById('memory-builder');
                                const quizBuilder = document.getElementById('quiz-builder');
                                const title = document.getElementById('builder-title');

                                builderArea.classList.remove('hidden');
                                memoryBuilder.classList.add('hidden');
                                quizBuilder.classList.add('hidden');

                                if (type === 'Game') {
                                    title.innerText = "Memory Game Builder";
                                    memoryBuilder.classList.remove('hidden');
                                    renderMemoryPairs();
                                    if (memoryPairs.length === 0) addMemoryPair(); 
                                } else if (type === 'Quiz') {
                                    title.innerText = "Quiz Game Builder";
                                    quizBuilder.classList.remove('hidden');
                                    renderQuizQuestions();
                                    if (quizQuestions.length === 0) addQuizQuestion();
                                } else {
                                    builderArea.classList.add('hidden');
                                }
                            }

                            // Memory Game Functions
                            function renderMemoryPairs() {
                                const container = document.getElementById('memory-pairs-container');
                                container.innerHTML = '';
                                memoryPairs.forEach(pair => addMemoryPair(pair.card1, pair.card2));
                            }

                            function addMemoryPair(val1 = '', val2 = '') {
                                const container = document.getElementById('memory-pairs-container');
                                const id = Date.now() + Math.random();
                                const html = `
                                    <div class="flex items-center gap-4 bg-white p-3 rounded shadow-sm border border-gray-200 pair-item" id="pair-${id}">
                                        <div class="flex-1">
                                            <input type="text" value="${val1}" placeholder="Card 1 (e.g. CPU)" class="card-1-input w-full text-sm rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div class="text-gray-400">↔</div>
                                        <div class="flex-1">
                                            <input type="text" value="${val2}" placeholder="Card 2 (e.g. Central Processing Unit)" class="card-2-input w-full text-sm rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <button type="button" onclick="this.closest('.pair-item').remove()" class="text-red-500 hover:text-red-700">
                                            🗑️
                                        </button>
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', html);
                            }

                            // Quiz Game Functions
                            function renderQuizQuestions() {
                                const container = document.getElementById('questions-container');
                                container.innerHTML = '';
                                quizQuestions.forEach(q => addQuizQuestion(q));
                            }

                            function addQuizQuestion(data = null) {
                                const container = document.getElementById('questions-container');
                                const id = Date.now() + Math.random();
                                
                                const qText = data ? data.question : '';
                                const answers = data ? data.answers : ['', '', '', ''];
                                const correct = data ? data.correctAnswer : 0;

                                const html = `
                                    <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm question-item" id="q-${id}">
                                        <div class="flex justify-between mb-3">
                                            <h4 class="font-bold text-gray-700">Question</h4>
                                            <button type="button" onclick="this.closest('.question-item').remove()" class="text-red-500 text-sm hover:underline">Remove Question</button>
                                        </div>
                                        <input type="text" value="${qText}" placeholder="Enter your question here..." class="question-input w-full mb-4 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-medium">
                                        
                                        <div class="space-y-2">
                                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Answers</label>
                                            ${[0, 1, 2, 3].map(i => `
                                                <div class="flex items-center gap-3">
                                                    <input type="radio" name="correct-${id}" value="${i}" ${i == correct ? 'checked' : ''} class="correct-radio text-green-600 focus:ring-green-500">
                                                    <input type="text" value="${answers[i]}" placeholder="Option ${i+1}" class="answer-input w-full text-sm rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                </div>
                                            `).join('')}
                                            <p class="text-xs text-gray-400 mt-1">* Select the radio button next to the correct answer.</p>
                                        </div>
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', html);
                            }

                            // Form Submission Handler
                            function prepareSubmission() {
                                try {
                                    const type = document.getElementById('type').value;
                                    const contentInput = document.getElementById('content');
                                    let data = {};

                                    if (type === 'Game') {
                                        // Serialize Memory Pairs
                                        const pairs = [];
                                        document.querySelectorAll('.pair-item').forEach(row => {
                                            const c1 = row.querySelector('.card-1-input').value;
                                            const c2 = row.querySelector('.card-2-input').value;
                                            if (c1 && c2) pairs.push({ card1: c1, card2: c2 });
                                        });
                                        
                                        data = {
                                            mode: "custom",
                                            gridSize: 4,
                                            customPairs: pairs
                                        };
                                    } else if (type === 'Quiz') {
                                        // Serialize Quiz Questions
                                        const questions = [];
                                        document.querySelectorAll('.question-item').forEach(block => {
                                            const qText = block.querySelector('.question-input').value;
                                            const answers = Array.from(block.querySelectorAll('.answer-input')).map(i => i.value);
                                            const correctIndex = Array.from(block.querySelectorAll('.correct-radio')).findIndex(r => r.checked);
                                            
                                            // Make sure at least question and some answer exists
                                            if (qText) {
                                                questions.push({
                                                    question: qText,
                                                    answers: answers,
                                                    correctAnswer: correctIndex
                                                });
                                            }
                                        });

                                        data = { questions: questions };
                                    }

                                    console.log('Submitting data:', data);
                                    contentInput.value = JSON.stringify(data);
                                    return true;
                                } catch (e) {
                                    console.error("Submission preparation failed:", e);
                                    alert("Error preparing data. Please check console.");
                                    return false;
                                }
                            }

                            // Initialize
                            toggleGameConfig();
                        </script>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('activities.index') }}" class="mr-4 text-gray-600 hover:text-gray-900">Batal</a>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Kemaskini Aktiviti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
