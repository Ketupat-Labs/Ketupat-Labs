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
            <?php echo e(__('Generate Slides with AI')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form id="slide-generator-form" class="space-y-6">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo e(__('Topic')); ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="topic" name="topic" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="<?php echo e(__('e.g., Introduction to Machine Learning')); ?>">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="number_of_slides" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php echo e(__('Number of Slides')); ?>

                                </label>
                                <input type="number" id="number_of_slides" name="number_of_slides" min="1" max="50" value="10"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="detail_level" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php echo e(__('Detail Level')); ?>

                                </label>
                                <select id="detail_level" name="detail_level"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="basic"><?php echo e(__('basic')); ?></option>
                                    <option value="intermediate" selected><?php echo e(__('intermediate')); ?></option>
                                    <option value="advanced"><?php echo e(__('advanced')); ?></option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" id="generate-btn"
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center">
                            <i class="fas fa-magic mr-2"></i>
                            <span id="generate-btn-text"><?php echo e(__('Generate Slides')); ?></span>
                            <span id="generate-btn-loading" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                <?php echo e(__('Generating...')); ?>

                            </span>
                        </button>
                    </form>

                    <div id="slides-result" class="hidden mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo e(__('Generated Slides')); ?></h3>
                            <button onclick="exportSlides()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i><?php echo e(__('Export')); ?>

                            </button>
                        </div>
                        <div id="slides-container" class="space-y-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
                    alert(data.message || '<?php echo e(__('Failed to generate slides')); ?>');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('<?php echo e(__('An error occurred while generating slides')); ?>');
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
                        <h4 class="text-lg font-semibold text-gray-900"><?php echo e(__('Slide')); ?> ${index + 1}: ${escapeHtml(slide.title || '<?php echo e(__('Untitled')); ?>')}</h4>
                        <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">#${index + 1}</span>
                    </div>
                    <div class="mb-3">
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            ${Array.isArray(slide.content) 
                                ? slide.content.map(point => `<li>${escapeHtml(point)}</li>`).join('')
                                : `<li>${escapeHtml(slide.content || '<?php echo e(__('No content')); ?>')}</li>`}
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

<?php /**PATH C:\xampp\htdocs\Material\resources\views/ai-generator/slides.blade.php ENDPATH**/ ?>