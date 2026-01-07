<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jana Slaid dengan AI') }}
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
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Muat Naik Dokumen (Tidak Wajib)') }}</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">{{ __('Muat naik dokumen TXT dan AI akan membacanya untuk menjana slaid berdasarkan kandungannya.') }}</p>
                        <p class="text-xs text-orange-600 mb-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Nota:</strong> TXT, PDF, dan DOCX disokong. PDF/DOCX mestilah mengandungi teks yang boleh dipilih (bukan imej yang diimbas). TXT adalah yang paling stabil.
                        </p>

                        <div class="flex items-center space-x-3">
                            <label for="document-upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>
                                {{ __('Pilih Fail') }}
                            </label>
                            <input type="file" id="document-upload" name="document" accept=".txt,.pdf,.docx,.doc" class="hidden" multiple>
                            <span id="file-name" class="text-sm text-gray-600 italic">{{ __('Tiada fail dipilih') }}</span>
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
                                    <i class="fas fa-check-circle mr-1"></i>{{ __('Sedia') }}
                                </span>
                            </div>
                            <div id="file-list" class="mt-2 space-y-1"></div>
                            
                            <!-- Page Range Selection (only for PDF files) -->
                            <div id="page-range-section" class="hidden mt-4 pt-4 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-file-pdf text-red-600 mr-1"></i>
                                    {{ __('Julat Halaman (Pilihan)') }}
                                </label>
                                <p class="text-xs text-gray-500 mb-2">{{ __('Nyatakan halaman mana yang ingin diekstrak. Biarkan kosong untuk menggunakan semua halaman.') }}</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="page_from" class="block text-xs text-gray-600 mb-1">{{ __('Dari Halaman') }}</label>
                                        <input type="number" id="page_from" name="page_from" min="1" 
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                               placeholder="{{ __('Halaman mula') }}">
                                    </div>
                                    <div>
                                        <label for="page_to" class="block text-xs text-gray-600 mb-1">{{ __('Ke Halaman') }}</label>
                                        <input type="number" id="page_to" name="page_to" min="1" 
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                               placeholder="{{ __('Halaman tamat') }}">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('Untuk fail DOCX/TXT, ini mewakili julat bahagian/perenggan.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 p-3 bg-blue-50 rounded border-l-4 border-blue-500">
                            <p class="text-xs text-blue-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Tip:</strong> Muat naik nota kuliah, buku teks, atau bahan pembelajaran anda. AI akan menganalisis dan mencipta slaid yang komprehensif!
                            </p>
                        </div>
                    </div>

                    <form id="slide-generator-form" class="space-y-6">
                        @csrf
                        <div>
                            <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Topik') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="topic" name="topic" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="{{ __('cth., Pengenalan kepada Machine Learning (atau biarkan kosong jika memuat naik dokumen)') }}">
                            <p class="text-xs text-gray-500 mt-1">{{ __('Jika anda memuat naik dokumen, AI akan menggunakan kandungannya. Jika tidak, sila nyatakan topik.') }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="number_of_slides" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Bilangan Slaid') }}
                                </label>
                                <input type="number" id="number_of_slides" name="number_of_slides" min="1" max="50" value="10"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="detail_level" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Tahap Perincian') }}
                                </label>
                                <select id="detail_level" name="detail_level"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="basic">{{ __('asas') }}</option>
                                    <option value="intermediate" selected>{{ __('sederhana') }}</option>
                                    <option value="advanced">{{ __('mendalam') }}</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" id="generate-btn"
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center">
                            <i class="fas fa-magic mr-2"></i>
                            <span id="generate-btn-text">{{ __('Jana Slaid') }}</span>
                            <span id="generate-btn-loading" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                {{ __('Menjana...') }}
                            </span>
                        </button>
                    </form>

                    <div id="slides-result" class="hidden mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Slaid Dijana') }}</h3>
                            <div class="flex items-center gap-2">
                                <select id="export-format" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <option value="pdf">PDF (Cetak)</option>
                                    <option value="pptx">PPTX</option>
                                    <option value="docx">DOCX</option>
                                    <option value="txt">TXT</option>
                                </select>
                                <button type="button" onclick="exportSlides()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="fas fa-download mr-2"></i>{{ __('Eksport') }}
                                </button>
                            </div>
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
            const pageRangeSection = document.getElementById('page-range-section');
            
            if (files.length > 0) {
                // Check total file size (allow up to 25MB to match backend limit of 20MB with some buffer)
                const totalSize = files.reduce((sum, file) => sum + file.size, 0);
                if (totalSize > 25 * 1024 * 1024) {
                    alert('{{ __('Jumlah saiz fail mestilah kurang daripada 25MB') }}');
                    e.target.value = '';
                    return;
                }

                // Check file types
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
                for (const file of files) {
                    if (!allowedTypes.includes(file.type)) {
                        alert('{{ __('Hanya fail PDF, DOCX, dan TXT sahaja yang dibenarkan') }}');
                        e.target.value = '';
                        return;
                    }
                }

                // Display file info
                fileName.textContent = files.length + ' fail dipilih';
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

                // Show page range section if PDF is uploaded
                const firstFile = files[0];
                if (firstFile && (firstFile.type === 'application/pdf' || firstFile.name.toLowerCase().endsWith('.pdf'))) {
                    pageRangeSection.classList.remove('hidden');
                } else {
                    pageRangeSection.classList.add('hidden');
                }

                // Make topic optional when document is uploaded
                topicInput.required = false;
                topicInput.placeholder = '{{ __('Pilihan - AI akan mengekstrak daripada dokumen') }}';
            } else {
                pageRangeSection.classList.add('hidden');
            }
        });

        clearFileBtn.addEventListener('click', function() {
            documentUpload.value = '';
            fileName.textContent = '{{ __('Tiada fail dipilih') }}';
            clearFileBtn.classList.add('hidden');
            documentPreview.classList.add('hidden');
            document.getElementById('file-list').innerHTML = '';
            topicInput.required = true;
            topicInput.placeholder = '{{ __('cth., Pengenalan kepada Machine Learning (atau biarkan kosong jika memuat naik dokumen)') }}';
        });

        document.getElementById('slide-generator-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const generateBtn = document.getElementById('generate-btn');
            const generateBtnText = document.getElementById('generate-btn-text');
            const generateBtnLoading = document.getElementById('generate-btn-loading');
            const resultDiv = document.getElementById('slides-result');
            const container = document.getElementById('slides-container');

            // Prepare form data first
            const formData = new FormData(form);

            // Add document file if uploaded
            const documentFile = documentUpload.files[0];
            if (documentFile) {
                formData.append('document', documentFile);
                
                // Add page range if specified
                const pageFrom = document.getElementById('page_from').value;
                const pageTo = document.getElementById('page_to').value;
                if (pageFrom) {
                    formData.append('page_from', pageFrom);
                }
                if (pageTo) {
                    formData.append('page_to', pageTo);
                }
            }

            // Show popup message
            alert('Slaid sedang dijana. Anda akan diarahkan ke halaman Slaid Dijana. Slaid akan muncul selepas penjanaan selesai.');

            // Show loading state briefly
            generateBtn.disabled = true;
            generateBtnText.classList.add('hidden');
            generateBtnLoading.classList.remove('hidden');
            resultDiv.classList.add('hidden');

            // Create a hidden iframe for truly non-blocking form submission
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.name = 'slide-generation-iframe-' + Date.now();
            document.body.appendChild(iframe);
            
            // Clone the original form and submit it to the iframe
            const originalForm = document.getElementById('slide-generator-form');
            const clonedForm = originalForm.cloneNode(true);
            clonedForm.id = 'temp-slide-form-' + Date.now();
            clonedForm.target = iframe.name;
            clonedForm.action = '/api/ai-generator/slides';
            clonedForm.method = 'POST';
            clonedForm.style.display = 'none';
            document.body.appendChild(clonedForm);
            
            // Submit the cloned form to iframe (completely non-blocking)
            clonedForm.submit();
            
            // Redirect immediately - iframe submission never blocks navigation
            window.location.replace('{{ route("ai-generator.slaid-dijana") }}');
        });

        function displaySlides(slides) {
            const container = document.getElementById('slides-container');
            container.innerHTML = '';

            slides.forEach((slide, index) => {
                const slideDiv = document.createElement('div');
                slideDiv.className = 'bg-gray-50 rounded-lg p-6 border border-gray-200';
                slideDiv.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="text-lg font-semibold text-gray-900">{{ __('Slaid') }} ${index + 1}: ${escapeHtml(slide.title || '{{ __('Tanpa Tajuk') }}')}</h4>
                        <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">#${index + 1}</span>
                    </div>
                    <div class="mb-3">
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            ${Array.isArray(slide.content)
                                ? slide.content.map(point => `<li>${escapeHtml(point)}</li>`).join('')
                                : `<li>${escapeHtml(slide.content || '{{ __('Tiada kandungan') }}')}</li>`}
                        </ul>
                    </div>
                    ${slide.summary ? `<p class="text-sm text-gray-600 italic">${escapeHtml(slide.summary)}</p>` : ''}
                `;
                container.appendChild(slideDiv);
            });
        }

        async function exportSlides() {
            // Prefer the original slide JSON from the last generation if available
            const slides = window.lastGeneratedSlides || Array.from(document.querySelectorAll('#slides-container > div')).map(div => {
                // Extract title by removing "Slide X: " prefix (works in both languages)
                const titleText = div.querySelector('h4').textContent;
                const title = titleText.replace(/^[^:]+: /, '');
                const content = Array.from(div.querySelectorAll('li')).map(li => li.textContent);
                const summary = div.querySelector('p.italic')?.textContent || '';
                return { title, content, summary };
            });

            const format = document.getElementById('export-format').value;
            const topic = document.getElementById('topic').value || 'slides';

            try {
                const response = await fetch('/api/ai-generator/slides/export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({ slides, topic, format })
                });

                if (!response.ok) {
                    // Try to parse JSON error
                    let msg = '{{ __('Gagal mengeksport slaid') }}';
                    try {
                        const err = await response.json();
                        msg = err.message || msg;
                    } catch (e) {
                        // ignore
                    }
                    alert(msg);
                    return;
                }

                const blob = await response.blob();
                const contentDisposition = response.headers.get('Content-Disposition') || '';
                const fileNameMatch = contentDisposition.match(/filename="?([^";]+)"?/i);
                const fileName = fileNameMatch ? fileNameMatch[1] : `slides.${format}`;

                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Export error:', error);
                alert('{{ __('Ralat berlaku semasa mengeksport slaid') }}');
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</x-app-layout>

