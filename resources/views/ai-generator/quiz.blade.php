<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Quiz with AI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form id="quiz-generator-form" class="space-y-6">
                        @csrf
                        <div>
                            <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Topic') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="topic" name="topic" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="{{ __('e.g., Python Programming Basics') }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="number_of_questions" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Number of Questions') }}
                                </label>
                                <input type="number" id="number_of_questions" name="number_of_questions" min="1" max="50" value="10"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>

                            <div>
                                <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Difficulty') }}
                                </label>
                                <select id="difficulty" name="difficulty"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="easy">{{ __('easy') }}</option>
                                    <option value="medium" selected>{{ __('medium') }}</option>
                                    <option value="hard">{{ __('hard') }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="question_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Question Type') }}
                                </label>
                                <select id="question_type" name="question_type"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="multiple_choice" selected>{{ __('multiple_choice') }}</option>
                                    <option value="true_false">{{ __('true_false') }}</option>
                                    <option value="mixed">{{ __('mixed') }}</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" id="generate-btn"
                                class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-3 px-6 rounded-lg font-semibold hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center">
                            <i class="fas fa-magic mr-2"></i>
                            <span id="generate-btn-text">{{ __('Generate Quiz') }}</span>
                            <span id="generate-btn-loading" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                {{ __('Generating...') }}
                            </span>
                        </button>
                    </form>

                    <div id="quiz-result" class="hidden mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Generated Quiz') }}</h3>
                            <button onclick="exportQuiz()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>{{ __('Export') }}
                            </button>
                        </div>
                        <div id="quiz-container" class="space-y-6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('quiz-generator-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const generateBtn = document.getElementById('generate-btn');
            const generateBtnText = document.getElementById('generate-btn-text');
            const generateBtnLoading = document.getElementById('generate-btn-loading');
            const resultDiv = document.getElementById('quiz-result');
            const container = document.getElementById('quiz-container');
            
            // Show loading state
            generateBtn.disabled = true;
            generateBtnText.classList.add('hidden');
            generateBtnLoading.classList.remove('hidden');
            resultDiv.classList.add('hidden');
            
            try {
                const formData = new FormData(form);
                const response = await fetch('/api/ai-generator/quiz', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 200 && data.data && data.data.quiz) {
                    displayQuiz(data.data.quiz);
                    resultDiv.classList.remove('hidden');
                } else {
                    alert(data.message || '{{ __('Failed to generate quiz') }}');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __('An error occurred while generating quiz') }}');
            } finally {
                generateBtn.disabled = false;
                generateBtnText.classList.remove('hidden');
                generateBtnLoading.classList.add('hidden');
            }
        });
        
        function displayQuiz(questions) {
            const container = document.getElementById('quiz-container');
            container.innerHTML = '';
            
            questions.forEach((question, index) => {
                const questionDiv = document.createElement('div');
                questionDiv.className = 'bg-gray-50 rounded-lg p-6 border border-gray-200';
                
                const optionsHtml = Array.isArray(question.options) 
                    ? question.options.map((option, optIndex) => {
                        const isCorrect = optIndex === question.correct_answer;
                        return `
                            <div class="flex items-center p-3 rounded ${isCorrect ? 'bg-green-100 border-2 border-green-500' : 'bg-white border border-gray-200'}">
                                <span class="font-semibold mr-3 ${isCorrect ? 'text-green-700' : 'text-gray-700'}">${String.fromCharCode(65 + optIndex)}.</span>
                                <span class="flex-1 ${isCorrect ? 'text-green-800 font-medium' : 'text-gray-700'}">${escapeHtml(option)}</span>
                                ${isCorrect ? '<i class="fas fa-check-circle text-green-600 ml-2"></i>' : ''}
                            </div>
                        `;
                    }).join('')
                    : '<p class="text-gray-500">{{ __('No options available') }}</p>';
                
                questionDiv.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="text-lg font-semibold text-gray-900">{{ __('Question') }} ${index + 1}</h4>
                        <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">#${index + 1}</span>
                    </div>
                    <p class="text-gray-800 mb-4 font-medium">${escapeHtml(question.question || '{{ __('No question text') }}')}</p>
                    <div class="space-y-2 mb-4">
                        ${optionsHtml}
                    </div>
                    ${question.explanation ? `
                        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded">
                            <p class="text-sm text-blue-800"><strong>{{ __('Explanation') }}:</strong> ${escapeHtml(question.explanation)}</p>
                        </div>
                    ` : ''}
                `;
                container.appendChild(questionDiv);
            });
        }
        
        function exportQuiz() {
            const questions = Array.from(document.querySelectorAll('#quiz-container > div')).map(div => {
                const question = div.querySelector('p.font-medium').textContent;
                const options = Array.from(div.querySelectorAll('.flex.items-center')).map(opt => {
                    return opt.querySelector('span.flex-1').textContent.trim();
                });
                const correctAnswer = Array.from(div.querySelectorAll('.flex.items-center')).findIndex(opt => 
                    opt.classList.contains('bg-green-100')
                );
                const explanation = div.querySelector('.bg-blue-50 p')?.textContent.replace('{{ __('Explanation') }}:', '').trim() || '';
                return { question, options, correct_answer: correctAnswer, explanation };
            });
            
            const dataStr = JSON.stringify(questions, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'quiz.json';
            link.click();
            URL.revokeObjectURL(url);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</x-app-layout>

