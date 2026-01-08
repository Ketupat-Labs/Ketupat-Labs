<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jana Kuiz dengan AI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Document Upload Section -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-green-50 rounded-lg border-2 border-dashed border-purple-300">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-file-upload text-purple-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Muat Naik Dokumen (Tidak Wajib)') }}</h3>
                        </div>

                        <!-- Warning about supported formats -->
                        <div class="mb-3 p-3 bg-orange-100 border-l-4 border-orange-500 text-orange-700">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span class="text-sm font-medium">{{ __('TXT, PDF, dan DOCX disokong. PDF/DOCX mestilah mengandungi teks yang boleh dipilih (bukan imej yang diimbas). format TXT adalah yang paling stabil.') }}</span>
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 mb-3">{{ __('Muat naik dokumen TXT/PDF/DOCX dengan teks yang boleh dibaca dan AI akan menjana soalan kuiz berdasarkan kandungannya.') }}</p>

                        <div class="flex items-center space-x-3">
                            <label for="document-upload-quiz" class="cursor-pointer inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>
                                {{ __('Pilih Fail') }}
                            </label>
                            <input type="file" id="document-upload-quiz" name="document" accept=".txt,.pdf,.docx,.doc" class="hidden">
                            <span id="file-name-quiz" class="text-sm text-gray-600 italic">{{ __('Tiada fail dipilih') }}</span>
                            <button type="button" id="clear-file-quiz" class="hidden text-red-600 hover:text-red-700">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>

                        <div id="document-preview-quiz" class="hidden mt-3 p-3 bg-white rounded border border-purple-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-file-alt text-purple-600"></i>
                                    <span id="preview-file-name-quiz" class="text-sm font-medium text-gray-700"></span>
                                    <span id="preview-file-size-quiz" class="text-xs text-gray-500"></span>
                                </div>
                                <span class="text-xs text-green-600 font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>{{ __('Sedia') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form id="quiz-generator-form" class="space-y-6">
                        @csrf
                        <div>
                            <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Topik') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="topic" name="topic" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="{{ __('cth., Asas Pemrograman Python (atau biarkan kosong jika memuat naik dokumen)') }}">
                            <p class="text-xs text-gray-500 mt-1">{{ __('Jika anda memuat naik dokumen, AI akan menggunakan kandungannya. Jika tidak, sila nyatakan topik.') }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="number_of_questions" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Bilangan Soalan') }}
                                </label>
                                <input type="number" id="number_of_questions" name="number_of_questions" min="1" max="50" value="10"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>

                            <div>
                                <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Tahap Kesukaran') }}
                                </label>
                                <select id="difficulty" name="difficulty"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="easy">{{ __('senang') }}</option>
                                    <option value="medium" selected>{{ __('sederhana') }}</option>
                                    <option value="hard">{{ __('susah') }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="question_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Jenis Soalan') }}
                                </label>
                                <select id="question_type" name="question_type"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="multiple_choice" selected>{{ __('pilihan jawapan objektif') }}</option>
                                    <option value="true_false">{{ __('benar/palsu') }}</option>
                                    <option value="mixed">{{ __('campuran') }}</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" id="generate-btn"
                                class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-3 px-6 rounded-lg font-semibold hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center">
                            <i class="fas fa-magic mr-2"></i>
                            <span id="generate-btn-text">{{ __('Jana Kuiz') }}</span>
                            <span id="generate-btn-loading" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                {{ __('Menjana...') }}
                            </span>
                        </button>
                    </form>

                    <div id="quiz-result" class="hidden mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Kuiz Dijana') }}</h3>
                            <div class="flex items-center gap-2">
                                <select id="quiz-export-format" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <option value="txt">TXT</option>
                                    <option value="docx">DOCX</option>
                                    <option value="pdf">PDF (Cetak)</option>
                                    <option value="pptx">PPTX</option>
                                </select>
                                <button onclick="exportQuiz()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="fas fa-download mr-2"></i>{{ __('Eksport') }}
                                </button>
                            </div>
                        </div>
                        <div id="quiz-container" class="space-y-6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle document upload for quiz
        const documentUploadQuiz = document.getElementById('document-upload-quiz');
        const fileNameQuiz = document.getElementById('file-name-quiz');
        const clearFileBtnQuiz = document.getElementById('clear-file-quiz');
        const documentPreviewQuiz = document.getElementById('document-preview-quiz');
        const previewFileNameQuiz = document.getElementById('preview-file-name-quiz');
        const previewFileSizeQuiz = document.getElementById('preview-file-size-quiz');
        const topicInputQuiz = document.getElementById('topic');

        documentUploadQuiz.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (max 10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert('{{ __('Saiz fail mestilah kurang daripada 10MB') }}');
                    e.target.value = '';
                    return;
                }

                // Check file type
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
                if (!allowedTypes.includes(file.type)) {
                    alert('{{ __('Hanya fail PDF, DOCX, dan TXT sahaja yang dibenarkan') }}');
                    e.target.value = '';
                    return;
                }

                fileNameQuiz.textContent = file.name;
                clearFileBtnQuiz.classList.remove('hidden');
                documentPreviewQuiz.classList.remove('hidden');
                previewFileNameQuiz.textContent = file.name;
                previewFileSizeQuiz.textContent = `(${(file.size / 1024).toFixed(2)} KB)`;

                // Make topic optional when document is uploaded
                topicInputQuiz.required = false;
                topicInputQuiz.placeholder = '{{ __('Pilihan - AI akan mengekstrak daripada dokumen') }}';
            }
        });

        clearFileBtnQuiz.addEventListener('click', function() {
            documentUploadQuiz.value = '';
            fileNameQuiz.textContent = '{{ __('Tiada fail dipilih') }}';
            clearFileBtnQuiz.classList.add('hidden');
            documentPreviewQuiz.classList.add('hidden');
            topicInputQuiz.required = true;
            topicInputQuiz.placeholder = '{{ __('cth., Asas Pemrograman Python (atau biarkan kosong jika memuat naik dokumen)') }}';
        });

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

                // Add document file if uploaded
                const documentFile = documentUploadQuiz.files[0];
                if (documentFile) {
                    formData.append('document', documentFile);
                }

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
                    window.lastGeneratedQuiz = data.data.quiz;
                    resultDiv.classList.remove('hidden');
                } else {
                    alert(data.message || '{{ __('Gagal menjana kuiz') }}');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __('Ralat berlaku semasa menjana kuiz') }}');
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
                    : '<p class="text-gray-500">{{ __('Tiada pilihan jawapan tersedia') }}</p>';

                questionDiv.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="text-lg font-semibold text-gray-900">{{ __('Soalan') }} ${index + 1}</h4>
                        <div class="flex items-center gap-2">
                            <button onclick="askKetupatAboutQuestion(${index}, '${escapeHtml(question.question || '').replace(/'/g, "\\'")}', '${escapeHtml(question.explanation || '').replace(/'/g, "\\'")}', event)"
                                    class="text-xs bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-3 py-1.5 rounded-full font-semibold transition-all duration-200 flex items-center gap-1.5 shadow-md hover:shadow-lg transform hover:scale-105"
                                    title="Tanya Ketupat untuk penjelasan lanjut">
                                <i class="fas fa-robot text-sm"></i>
                                <span>Tanya Ketupat</span>
                            </button>
                            <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">#${index + 1}</span>
                        </div>
                    </div>
                    <p class="text-gray-800 mb-4 font-medium">${escapeHtml(question.question || '{{ __('Tiada teks soalan') }}')}</p>
                    <div class="space-y-2 mb-4">
                        ${optionsHtml}
                    </div>
                    ${question.explanation ? `
                        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded">
                            <p class="text-sm text-blue-800"><strong>{{ __('Penjelasan') }}:</strong> ${escapeHtml(question.explanation)}</p>
                        </div>
                    ` : ''}
                `;
                container.appendChild(questionDiv);
            });
        }

        async function exportQuiz() {
            const quiz = window.lastGeneratedQuiz;
            if (!quiz || !Array.isArray(quiz) || quiz.length === 0) {
                alert('{{ __('Sila jana kuiz terlebih dahulu sebelum mengeksport.') }}');
                return;
            }

            const format = document.getElementById('quiz-export-format')?.value || 'txt';
            const topic = document.getElementById('topic')?.value || 'quiz';

            try {
                const response = await fetch('/api/ai-generator/quiz/export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/octet-stream',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ quiz, topic, format })
                });

                if (!response.ok) {
                    let message = '{{ __('Gagal mengeksport kuiz') }}';
                    try {
                        const err = await response.json();
                        message = err.message || message;
                    } catch (e) {
                        // ignore
                    }
                    alert(message);
                    return;
                }

                const contentDisposition = response.headers.get('Content-Disposition') || '';
                const match = contentDisposition.match(/filename="?([^";]+)"?/i);
                const fileName = match ? match[1] : `kuiz.${format}`;

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Export error:', error);
                alert('{{ __('Ralat berlaku semasa mengeksport kuiz') }}');
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Ask Ketupat about a quiz question
        function askKetupatAboutQuestion(questionIndex, questionText, explanation, event) {
            event.preventDefault();
            event.stopPropagation();

            // Build a contextual message for Ketupat
            let contextMessage = `Saya ada soalan tentang kuiz ini:\n\n`;
            contextMessage += `Soalan ${questionIndex + 1}: ${questionText}\n\n`;

            if (explanation) {
                contextMessage += `Penjelasan yang diberikan: ${explanation}\n\n`;
            }

            contextMessage += `Boleh terangkan dengan lebih lanjut?`;

            // Check if chatbot toggle function exists
            if (typeof toggleChatbot === 'function') {
                // Open the chatbot
                const chatbotWindow = document.getElementById('chatbot-window');
                if (chatbotWindow && chatbotWindow.classList.contains('hidden')) {
                    toggleChatbot();
                }

                // Wait a bit for chatbot to open, then set the message
                setTimeout(() => {
                    const chatInput = document.getElementById('chatbot-input');

                    if (chatInput) {
                        chatInput.value = contextMessage;
                        chatInput.focus();

                        // Trigger input event to update any character counts
                        chatInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }, 300);
            } else {
                // Fallback: Alert user to use the chatbot button
                alert('Sila buka chatbot Ketupat (butang di sudut kanan bawah) untuk bertanya soalan tentang kuiz ini.\n\n' + contextMessage);
            }
        }
    </script>
</x-app-layout>

