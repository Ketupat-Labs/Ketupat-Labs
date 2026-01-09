<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Header with Back Button -->
                    <div class="flex justify-between items-center mb-6 border-b-2 border-[#2454FF] pb-3">
                        <h2 class="text-2xl font-bold text-gray-800">
                            Cipta Aktiviti Baru
                        </h2>
                        <a href="{{ route('lessons.index', ['tab' => 'activities']) }}"
                            class="text-gray-600 hover:text-gray-900 font-medium">
                            ← Kembali
                        </a>
                    </div>

                    <form id="create-activity-form" action="{{ route('activities.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Tajuk Aktiviti</label>
                            <input type="text" name="title" id="title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required placeholder="Contoh: Permainan Matematik">
                        </div>

                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700">Jenis Aktiviti</label>
                            <select name="type" id="type" onchange="toggleGameConfig()"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="Game">Memory Game</option>
                                <option value="Quiz">Quiz Game</option>
                                <option value="Exercise">Latihan</option>
                                <option value="Video">Video</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="suggested_duration" class="block text-sm font-medium text-gray-700">Cadangan
                                Masa</label>
                            <input type="text" name="suggested_duration" id="suggested_duration"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required placeholder="Contoh: 30 Minit">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Penerangan
                                (Pilihan)</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                        </div>
                        
                        <!-- Badge Selection -->
                        <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="flex justify-between items-center mb-2">
                                <label for="badge_id" class="block text-sm font-bold text-gray-800">
                                    🏆 Penganugerahan Lencana (Pilihan)
                                </label>
                                <a href="{{ route('badges.create') }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                                    + Cipta Lencana Baru
                                </a>
                            </div>
                            <p class="text-xs text-gray-600 mb-2">Pelajar akan menerima lencana ini secara automatik apabila menyelesaikan aktiviti.</p>
                            
                            <select name="badge_id" id="badge_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Tiada Lencana --</option>
                                @if(isset($availableBadges) && $availableBadges->count() > 0)
                                    @foreach($availableBadges as $badge)
                                        <option value="{{ $badge->id }}">
                                            {{ $badge->icon }} {{ $badge->name }} ({{ $badge->xp_reward }} XP)
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>Tiada lencana tersedia (Cipta lencana baru dahulu)</option>
                                @endif
                            </select>
                        </div>

                        <!-- Hidden JSON Input -->
                        <textarea name="content" id="content" class="hidden"></textarea>

                        <!-- Visual Game Builder -->
                        <div id="game-builder-area"
                            class="mb-6 bg-gray-50 p-6 rounded-lg border-2 border-dashed border-gray-300 hidden">
                            <h3 class="text-lg font-bold text-gray-800 mb-4" id="builder-title">Game Configuration</h3>

                            <!-- Memory Game Builder -->
                            <div id="memory-builder" class="hidden">
                                <!-- Step 1: Configuration Options -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="memory_theme" class="block text-sm font-medium text-gray-700">Tema
                                            Pratetap</label>
                                        <select id="memory_theme" onchange="toggleMemoryTheme()"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="custom">Tersuai (Gambar/Teks Sendiri)</option>
                                            <option value="animals">Haiwan</option>
                                            <option value="fruits">Buah-buahan</option>
                                            <option value="shapes">Bentuk</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="memory_grid_size"
                                            class="block text-sm font-medium text-gray-700">Saiz Grid</label>
                                        <select id="memory_grid_size"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="4x4">4x4 (Mudah - 8 Pasangan)</option>
                                            <option value="6x6">6x6 (Sederhana - 18 Pasangan)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Step 2: Custom Pairs (Only shown if custom is selected) -->
                                <div id="custom-pairs-wrapper">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Pasangan Tersuai
                                            (Custom Matching Pairs)</label>
                                        <div id="memory-pairs-container" class="space-y-3">
                                            <!-- Pairs will be added here -->
                                        </div>
                                        <button type="button" onclick="addMemoryPair()"
                                            class="mt-3 text-sm flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                                            <span class="text-xl mr-1">+</span> Tambah Pasangan Baru
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Quiz Game Builder -->
                            <div id="quiz-builder" class="hidden">
                                <div id="questions-container" class="space-y-6">
                                    <!-- Questions will be added here -->
                                </div>
                                <button type="button" onclick="addQuizQuestion()"
                                    class="mt-4 w-full py-3 border-2 border-dashed border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 font-semibold transition">
                                    + Add New Question
                                </button>
                            </div>
                        </div>

                        <script>
                            // State
                            let memoryPairs = [];
                            let quizQuestions = [];

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
                                    if (memoryPairs.length === 0) addMemoryPair(); // Start with one
                                } else if (type === 'Quiz') {
                                    title.innerText = "Quiz Game Builder";
                                    quizBuilder.classList.remove('hidden');
                                    if (quizQuestions.length === 0) addQuizQuestion(); // Start with one
                                } else {
                                    builderArea.classList.add('hidden');
                                }
                            }

                            // Memory Game Functions
                            function toggleMemoryTheme() {
                                const theme = document.getElementById('memory_theme').value;
                                const customWrapper = document.getElementById('custom-pairs-wrapper');
                                if (theme === 'custom') {
                                    customWrapper.classList.remove('hidden');
                                } else {
                                    customWrapper.classList.add('hidden');
                                }
                            }

                            function addMemoryPair() {
                                const container = document.getElementById('memory-pairs-container');
                                const id = Date.now();
                                const html = `
                                    <div class="flex items-center gap-4 bg-white p-3 rounded shadow-sm border border-gray-200 pair-item" id="pair-${id}">
                                        <div class="flex-1">
                                            <input type="text" placeholder="Card 1 (e.g. CPU)" class="card-1-input w-full text-sm rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div class="text-gray-400">↔</div>
                                        <div class="flex-1">
                                            <input type="text" placeholder="Card 2 (e.g. Central Processing Unit)" class="card-2-input w-full text-sm rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <button type="button" onclick="this.closest('.pair-item').remove()" class="text-red-500 hover:text-red-700">
                                            🗑️
                                        </button>
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', html);
                            }

                            // Quiz Game Functions
                            function addQuizQuestion() {
                                const container = document.getElementById('questions-container');
                                const id = Date.now();
                                const html = `
                                    <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm question-item" id="q-${id}">
                                        <div class="flex justify-between mb-3">
                                            <h4 class="font-bold text-gray-700">Question</h4>
                                            <button type="button" onclick="this.closest('.question-item').remove()" class="text-red-500 text-sm hover:underline">Remove Question</button>
                                        </div>
                                        <input type="text" placeholder="Enter your question here..." class="question-input w-full mb-4 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-medium">
                                        
                                        <div class="space-y-2">
                                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Answers</label>
                                            ${[0, 1, 2, 3].map(i => `
                                                <div class="flex items-center gap-3">
                                                    <input type="radio" name="correct-${id}" value="${i}" ${i === 0 ? 'checked' : ''} class="correct-radio text-green-600 focus:ring-green-500">
                                                    <input type="text" value="${answers[i] || ''}" placeholder="Option ${i+1}" class="answer-input w-full text-sm rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                </div>
                                            `).join('')}
                                            <p class="text-xs text-gray-400 mt-1">* Select the radio button next to the correct answer.</p>
                                        </div>
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', html);
                            }

                            // Form Submission Handler
                            document.getElementById('create-activity-form').addEventListener('submit', function (e) {
                                const type = document.getElementById('type').value;
                                const contentInput = document.getElementById('content');
                                let data = {};

                                if (type === 'Game') {
                                    const theme = document.getElementById('memory_theme').value;
                                    const gridSizeVal = document.getElementById('memory_grid_size').value; // e.g., "4x4"

                                    // Parse grid size to integer (e.g., "4x4" -> 4)
                                    const gridInt = parseInt(gridSizeVal.split('x')[0]);

                                    let pairs = [];
                                    if (theme === 'custom') {
                                        // Serialize Memory Pairs only for custom theme
                                        document.querySelectorAll('.pair-item').forEach(row => {
                                            const c1 = row.querySelector('.card-1-input').value.trim();
                                            const c2 = row.querySelector('.card-2-input').value.trim();
                                            if (c1 && c2) pairs.push({ card1: c1, card2: c2 });
                                        });

                                        if (pairs.length === 0) {
                                            e.preventDefault();
                                            alert('Sila tambah sekurang-kurangnya satu pasangan untuk Memory Game Tersuai.');
                                            return false;
                                        }
                                    }

                                    data = {
                                        mode: theme === 'custom' ? "custom" : "preset",
                                        theme: theme, // 'custom', 'animals', etc.
                                        gridSize: gridInt, // 4 or 6
                                        customPairs: theme === 'custom' ? pairs : []
                                    };
                                } else if (type === 'Quiz') {
                                // Serialize Quiz Questions
                                const questions = [];
                                document.querySelectorAll('.question-item').forEach(block => {
                                    const qText = block.querySelector('.question-input').value.trim();
                                    const answers = Array.from(block.querySelectorAll('.answer-input')).map(i => i.value.trim()).filter(a => a);
                                    const correctIndex = Array.from(block.querySelectorAll('.correct-radio')).findIndex(r => r.checked);

                                    if (qText && answers.length > 0) {
                                        questions.push({
                                            question: qText,
                                            answers: answers,
                                            correctAnswer: correctIndex >= 0 ? correctIndex : 0
                                        });
                                    }
                                });

                                if (questions.length === 0) {
                                    e.preventDefault();
                                    alert('Sila tambah sekurang-kurangnya satu soalan untuk Quiz Game.');
                                    return false;
                                }

                                data = { questions: questions };
                            } else {
                                // For Exercise and Video types, use empty object instead of empty string
                                data = {};
                            }

                            // Always set valid JSON (empty object if no content)
                            contentInput.value = JSON.stringify(data);

                            // Debug: Log the content being sent
                            console.log('Saving activity with content:', contentInput.value);
                            });

                            // Initialize
                            toggleGameConfig();
                            toggleMemoryTheme();
                        </script>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('lessons.index', ['tab' => 'activities']) }}"
                                class="mr-4 text-gray-600 hover:text-gray-900">Batal</a>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Simpan Aktiviti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>