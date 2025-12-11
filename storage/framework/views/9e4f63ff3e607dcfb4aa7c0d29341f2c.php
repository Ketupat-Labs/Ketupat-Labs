<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\AppLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Ciri AI - Penjanaan Slaid & Kuiz')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Ciri AI</h1>
                        <p class="text-gray-600">Gunakan AI untuk menjana slaid pembelajaran dan kuiz secara automatik</p>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button onclick="switchTab('slides')" id="tab-slides"
                                class="tab-button active border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
                                <i class="fas fa-presentation mr-2"></i>Jana Slaid
                            </button>
                            <button onclick="switchTab('quiz')" id="tab-quiz"
                                class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                <i class="fas fa-question-circle mr-2"></i>Jana Kuiz
                            </button>
                        </nav>
                    </div>

                    <!-- Slides Generation Tab -->
                    <div id="slides-tab" class="tab-content">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 mb-6">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-4">
                                <i class="fas fa-presentation text-blue-600 mr-2"></i>Penjanaan Slaid AI
                            </h2>
                            <p class="text-gray-700 mb-6">Masukkan topik dan AI akan menjana slaid pembelajaran yang lengkap untuk anda.</p>
                            
                            <form id="slides-form" class="space-y-4">
                                <div>
                                    <label for="slides-topic" class="block text-sm font-medium text-gray-700 mb-2">
                                        Topik <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="slides-topic" name="topic" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Contoh: Pengenalan kepada Human-Computer Interaction">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="slides-number" class="block text-sm font-medium text-gray-700 mb-2">
                                            Bilangan Slaid
                                        </label>
                                        <input type="number" id="slides-number" name="number_of_slides" min="1" max="50" value="10"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label for="slides-level" class="block text-sm font-medium text-gray-700 mb-2">
                                            Tahap Kesukaran
                                        </label>
                                        <select id="slides-level" name="level"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="beginner">Pemula</option>
                                            <option value="intermediate" selected>Pertengahan</option>
                                            <option value="advanced">Lanjutan</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" id="generate-slides-btn"
                                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 flex items-center justify-center">
                                    <i class="fas fa-magic mr-2"></i>
                                    <span id="slides-btn-text">Jana Slaid</span>
                                    <span id="slides-loading" class="hidden ml-2">
                                        <i class="fas fa-spinner fa-spin"></i> Menjana...
                                    </span>
                                </button>
                            </form>
                        </div>

                        <!-- Slides Results -->
                        <div id="slides-results" class="hidden mt-6">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-xl font-semibold text-gray-900">Slaid yang Dijana</h3>
                                    <button onclick="exportSlides()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                        <i class="fas fa-download mr-2"></i>Eksport
                                    </button>
                                </div>
                                <div id="slides-content" class="space-y-4"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Quiz Generation Tab -->
                    <div id="quiz-tab" class="tab-content hidden">
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6 mb-6">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-4">
                                <i class="fas fa-question-circle text-purple-600 mr-2"></i>Penjanaan Kuiz AI
                            </h2>
                            <p class="text-gray-700 mb-6">Masukkan topik dan AI akan menjana soalan kuiz yang sesuai untuk anda.</p>
                            
                            <form id="quiz-form" class="space-y-4">
                                <div>
                                    <label for="quiz-topic" class="block text-sm font-medium text-gray-700 mb-2">
                                        Topik <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="quiz-topic" name="topic" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="Contoh: Prinsip-prinsip Reka Bentuk Antara Muka">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="quiz-number" class="block text-sm font-medium text-gray-700 mb-2">
                                            Bilangan Soalan
                                        </label>
                                        <input type="number" id="quiz-number" name="number_of_questions" min="1" max="20" value="5"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label for="quiz-difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                                            Tahap Kesukaran
                                        </label>
                                        <select id="quiz-difficulty" name="difficulty"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="easy">Mudah</option>
                                            <option value="medium" selected>Pertengahan</option>
                                            <option value="hard">Sukar</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" id="generate-quiz-btn"
                                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all duration-200 flex items-center justify-center">
                                    <i class="fas fa-magic mr-2"></i>
                                    <span id="quiz-btn-text">Jana Kuiz</span>
                                    <span id="quiz-loading" class="hidden ml-2">
                                        <i class="fas fa-spinner fa-spin"></i> Menjana...
                                    </span>
                                </button>
                            </form>
                        </div>

                        <!-- Quiz Results -->
                        <div id="quiz-results" class="hidden mt-6">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-xl font-semibold text-gray-900">Kuiz yang Dijana</h3>
                                    <button onclick="exportQuiz()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                        <i class="fas fa-download mr-2"></i>Eksport
                                    </button>
                                </div>
                                <div id="quiz-content" class="space-y-6"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tab-button.active {
            border-bottom-color: #3B82F6;
            color: #2563EB;
        }
        .tab-content {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .slide-card {
            border-left: 4px solid #3B82F6;
        }
        .quiz-card {
            border-left: 4px solid #9333EA;
        }
    </style>

    <script>
        function switchTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
            document.querySelectorAll('.tab-button').forEach(b => {
                b.classList.remove('active', 'border-blue-500', 'text-blue-600');
                b.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected tab
            if (tab === 'slides') {
                document.getElementById('slides-tab').classList.remove('hidden');
                document.getElementById('tab-slides').classList.add('active', 'border-blue-500', 'text-blue-600');
                document.getElementById('tab-slides').classList.remove('border-transparent', 'text-gray-500');
            } else {
                document.getElementById('quiz-tab').classList.remove('hidden');
                document.getElementById('tab-quiz').classList.add('active', 'border-blue-500', 'text-blue-600');
                document.getElementById('tab-quiz').classList.remove('border-transparent', 'text-gray-500');
            }
        }

        // Slides form submission
        document.getElementById('slides-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('generate-slides-btn');
            const btnText = document.getElementById('slides-btn-text');
            const loading = document.getElementById('slides-loading');
            const results = document.getElementById('slides-results');
            const content = document.getElementById('slides-content');
            
            btn.disabled = true;
            btnText.classList.add('hidden');
            loading.classList.remove('hidden');
            results.classList.add('hidden');
            
            try {
                const formData = new FormData(this);
                const data = Object.fromEntries(formData);
                
                const response = await fetch('/api/ai-features/generate-slides', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 200 && result.data.slides) {
                    displaySlides(result.data.slides);
                    results.classList.remove('hidden');
                } else {
                    alert(result.message || 'Ralat semasa menjana slaid. Sila cuba lagi.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ralat sambungan. Sila semak sambungan internet anda.');
            } finally {
                btn.disabled = false;
                btnText.classList.remove('hidden');
                loading.classList.add('hidden');
            }
        });

        // Quiz form submission
        document.getElementById('quiz-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('generate-quiz-btn');
            const btnText = document.getElementById('quiz-btn-text');
            const loading = document.getElementById('quiz-loading');
            const results = document.getElementById('quiz-results');
            const content = document.getElementById('quiz-content');
            
            btn.disabled = true;
            btnText.classList.add('hidden');
            loading.classList.remove('hidden');
            results.classList.add('hidden');
            
            try {
                const formData = new FormData(this);
                const data = Object.fromEntries(formData);
                
                const response = await fetch('/api/ai-features/generate-quiz', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 200 && result.data.quiz) {
                    displayQuiz(result.data.quiz);
                    results.classList.remove('hidden');
                } else {
                    alert(result.message || 'Ralat semasa menjana kuiz. Sila cuba lagi.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ralat sambungan. Sila semak sambungan internet anda.');
            } finally {
                btn.disabled = false;
                btnText.classList.remove('hidden');
                loading.classList.add('hidden');
            }
        });

        function displaySlides(slides) {
            const content = document.getElementById('slides-content');
            content.innerHTML = slides.map((slide, index) => `
                <div class="slide-card bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="text-lg font-semibold text-gray-900">Slaid ${index + 1}: ${escapeHtml(slide.title || 'Tanpa Tajuk')}</h4>
                        <span class="text-sm text-gray-500">#${index + 1}</span>
                    </div>
                    <div class="mb-4">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Kandungan:</h5>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            ${(slide.content || []).map(point => `<li>${escapeHtml(point)}</li>`).join('')}
                        </ul>
                    </div>
                    ${slide.key_takeaways && slide.key_takeaways.length > 0 ? `
                        <div>
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Pembelajaran Utama:</h5>
                            <ul class="list-disc list-inside space-y-1 text-blue-600">
                                ${slide.key_takeaways.map(takeaway => `<li>${escapeHtml(takeaway)}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        function displayQuiz(quiz) {
            const content = document.getElementById('quiz-content');
            content.innerHTML = quiz.map((question, index) => `
                <div class="quiz-card bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-start justify-between mb-4">
                        <h4 class="text-lg font-semibold text-gray-900">Soalan ${index + 1}</h4>
                        <span class="text-sm text-gray-500">#${index + 1}</span>
                    </div>
                    <p class="text-gray-800 mb-4 font-medium">${escapeHtml(question.question || 'Tanpa soalan')}</p>
                    <div class="space-y-2">
                        ${(question.options || []).map((option, optIndex) => `
                            <div class="flex items-center p-3 rounded-lg ${optIndex === question.correct_answer ? 'bg-green-100 border-2 border-green-500' : 'bg-white border border-gray-200'}">
                                <span class="font-semibold mr-3 ${optIndex === question.correct_answer ? 'text-green-700' : 'text-gray-600'}">
                                    ${String.fromCharCode(65 + optIndex)}.
                                </span>
                                <span class="${optIndex === question.correct_answer ? 'text-green-800 font-medium' : 'text-gray-700'}">
                                    ${escapeHtml(option)}
                                    ${optIndex === question.correct_answer ? '<span class="ml-2 text-green-600"><i class="fas fa-check-circle"></i> Jawapan Betul</span>' : ''}
                                </span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('');
        }

        function exportSlides() {
            const slides = document.getElementById('slides-content').innerHTML;
            const blob = new Blob([slides], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'slides.html';
            a.click();
        }

        function exportQuiz() {
            const quiz = document.getElementById('quiz-content').innerHTML;
            const blob = new Blob([quiz], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'quiz.html';
            a.click();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>

<?php /**PATH C:\xampp\htdocs\Material\resources\views/ai-features/index.blade.php ENDPATH**/ ?>