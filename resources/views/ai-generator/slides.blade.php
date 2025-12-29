<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Slides with AI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Document Upload Section -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg border-2 border-dashed border-purple-300">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-file-upload text-purple-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Upload Document (Optional)') }}</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">{{ __('Upload a document (PDF, DOCX, TXT) and AI will read it to generate slides based on its content.') }}</p>
                        
                        <div class="flex items-center space-x-3">
                            <label for="document-upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>
                                {{ __('Choose File') }}
                            </label>
                            <input type="file" id="document-upload" name="document" accept=".pdf,.docx,.doc,.txt" class="hidden" multiple>
                            <span id="file-name" class="text-sm text-gray-600 italic">{{ __('No file chosen') }}</span>
                            <button type="button" id="clear-file" class="hidden text-red-600 hover:text-red-700">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                        
                        <div id="document-preview" class="hidden mt-3 p-3 bg-white rounded border border-purple-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-file-alt text-purple-600"></i>
                                    <span id="preview-file-name" class="text-sm font-medium text-gray-700"></span>
                                    <span id="preview-file-size" class="text-xs text-gray-500"></span>
                                </div>
                                <span class="text-xs text-green-600 font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>{{ __('Ready') }}
                                </span>
                            </div>
                            <div id="file-list" class="mt-2 space-y-1"></div>
                        </div>
                        
                        <div class="mt-3 p-3 bg-blue-50 rounded border-l-4 border-blue-500">
                            <p class="text-xs text-blue-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Tip:</strong> Upload your lecture notes, textbooks, or study materials. AI will analyze and create comprehensive slides!
                            </p>
                        </div>
                    </div>

                    <form id="slide-generator-form" class="space-y-6">
                        @csrf
                        <div>
                            <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Topic') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="topic" name="topic" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="{{ __('e.g., Introduction to Machine Learning (or leave empty if uploading document)') }}">
                            <p class="text-xs text-gray-500 mt-1">{{ __('If you upload a document, the AI will use its content. Otherwise, provide a topic.') }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="number_of_slides" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Number of Slides') }}
                                </label>
                                <input type="number" id="number_of_slides" name="number_of_slides" min="1" max="50" value="10"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="detail_level" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Detail Level') }}
                                </label>
                                <select id="detail_level" name="detail_level"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="basic">{{ __('basic') }}</option>
                                    <option value="intermediate" selected>{{ __('intermediate') }}</option>
                                    <option value="advanced">{{ __('advanced') }}</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" id="generate-btn"
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center">
                            <i class="fas fa-magic mr-2"></i>
                            <span id="generate-btn-text">{{ __('Generate Slides') }}</span>
                            <span id="generate-btn-loading" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                {{ __('Generating...') }}
                            </span>
                        </button>
                    </form>

                    <div id="slides-result" class="hidden mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Generated Slides') }}</h3>
                            <button onclick="exportSlides()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>{{ __('Export') }}
                            </button>
                        </div>
                        <div id="slides-container" class="space-y-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle document upload
        const documentUpload = document.getElementById('document-upload');
        const fileName = document.getElementById('file-name');
        const clearFileBtn = document.getElementById('clear-file');
        const documentPreview = document.getElementById('document-preview');
        const previewFileName = document.getElementById('preview-file-name');
        const previewFileSize = document.getElementById('preview-file-size');
        const topicInput = document.getElementById('topic');
        
        documentUpload.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                // Check total file size
                const totalSize = files.reduce((sum, file) => sum + file.size, 0);
                if (totalSize > 50 * 1024 * 1024) {
                    alert('{{ __('Total file size must be less than 50MB') }}');
                    e.target.value = '';
                    return;
                }
                
                // Check file types
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
                for (const file of files) {
                    if (!allowedTypes.includes(file.type)) {
                        alert('{{ __('Only PDF, DOCX, and TXT files are allowed') }}');
                        e.target.value = '';
                        return;
                    }
                }
                
                // Display file info
                fileName.textContent = files.length + ' file(s) selected';
                clearFileBtn.classList.remove('hidden');
                documentPreview.classList.remove('hidden');
                
                // Show file list
                const fileListDiv = document.getElementById('file-list');
                fileListDiv.innerHTML = '';
                files.forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'flex items-center justify-between text-xs';
                    fileItem.innerHTML = `
                        <span class="text-gray-700">${index + 1}. ${file.name}</span>
                        <span class="text-gray-500">(${(file.size / 1024).toFixed(2)} KB)</span>
                    `;
                    fileListDiv.appendChild(fileItem);
                });
                
                // Make topic optional when document is uploaded
                topicInput.required = false;
                topicInput.placeholder = '{{ __('Optional - AI will extract from document') }}';
            }
        });
        
        clearFileBtn.addEventListener('click', function() {
            documentUpload.value = '';
            fileName.textContent = '{{ __('No file chosen') }}';
            clearFileBtn.classList.add('hidden');
            documentPreview.classList.add('hidden');
            document.getElementById('file-list').innerHTML = '';
            topicInput.required = true;
            topicInput.placeholder = '{{ __('e.g., Introduction to Machine Learning (or leave empty if uploading document)') }}';
        });
        
        document.getElementById('slide-generator-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const generateBtn = document.getElementById('generate-btn');
            const generateBtnText = document.getElementById('generate-btn-text');
            const generateBtnLoading = document.getElementById('generate-btn-loading');
            const resultDiv = document.getElementById('slides-result');
            const container = document.getElementById('slides-container');
            
            // Show loading state
            generateBtn.disabled = true;
            generateBtnText.classList.add('hidden');
            generateBtnLoading.classList.remove('hidden');
            resultDiv.classList.add('hidden');
            
            try {
                const formData = new FormData(form);
                
                // Add document file if uploaded
                const documentFile = documentUpload.files[0];
                if (documentFile) {
                    formData.append('document', documentFile);
                }
                
                const response = await fetch('/api/ai-generator/slides', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 200 && data.data && data.data.slides) {
                    displaySlides(data.data.slides);
                    resultDiv.classList.remove('hidden');
                } else {
                    alert(data.message || '{{ __('Failed to generate slides') }}');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __('An error occurred while generating slides') }}');
            } finally {
                generateBtn.disabled = false;
                generateBtnText.classList.remove('hidden');
                generateBtnLoading.classList.add('hidden');
            }
        });
        
        function displaySlides(slides) {
            const container = document.getElementById('slides-container');
            container.innerHTML = '';
            
            slides.forEach((slide, index) => {
                const slideDiv = document.createElement('div');
                slideDiv.className = 'bg-gray-50 rounded-lg p-6 border border-gray-200';
                slideDiv.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="text-lg font-semibold text-gray-900">{{ __('Slide') }} ${index + 1}: ${escapeHtml(slide.title || '{{ __('Untitled') }}')}</h4>
                        <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">#${index + 1}</span>
                    </div>
                    <div class="mb-3">
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            ${Array.isArray(slide.content) 
                                ? slide.content.map(point => `<li>${escapeHtml(point)}</li>`).join('')
                                : `<li>${escapeHtml(slide.content || '{{ __('No content') }}')}</li>`}
                        </ul>
                    </div>
                    ${slide.summary ? `<p class="text-sm text-gray-600 italic">${escapeHtml(slide.summary)}</p>` : ''}
                `;
                container.appendChild(slideDiv);
            });
        }
        
        function exportSlides() {
            const slides = Array.from(document.querySelectorAll('#slides-container > div')).map(div => {
                // Extract title by removing "Slide X: " prefix (works in both languages)
                const titleText = div.querySelector('h4').textContent;
                const title = titleText.replace(/^[^:]+: /, '');
                const content = Array.from(div.querySelectorAll('li')).map(li => li.textContent);
                const summary = div.querySelector('p.italic')?.textContent || '';
                return { title, content, summary };
            });
            
            const dataStr = JSON.stringify(slides, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'slides.json';
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

