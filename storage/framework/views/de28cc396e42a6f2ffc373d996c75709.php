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
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-start pb-4 border-b border-gray-200 mb-4">
                <h2 class="text-3xl font-extrabold text-[#2454FF]">
                    <?php echo e($lesson->title); ?> <span class="text-base text-gray-500">(<?php echo e($lesson->topic); ?>)</span>
                </h2>
                <a href="<?php echo e(route('lessons.index')); ?>" class="text-[#5FAD56] hover:text-green-700 font-medium">
                    &larr; Back to Lesson List
                </a>
            </div>

            
            <div class="progress-bar-area bg-gray-200 h-6 rounded-full mb-6">
                <div id="progress-fill" class="progress-fill h-full text-center text-white bg-[#5FAD56] rounded-full transition-all duration-300"
                    style="width: 0%;">
                    <span id="progress-text">0% Complete</span>
                </div>
            </div>

            <div class="lesson-content-card space-y-6">
                
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">Lesson Content</h3>
                    
                    
                    <div class="mt-6 space-y-6">
                        <?php if(isset($lesson->content_blocks['blocks']) && count($lesson->content_blocks['blocks']) > 0): ?>
                            <?php $__currentLoopData = $lesson->content_blocks['blocks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="lesson-block">
                                    <?php if($block['type'] === 'heading'): ?>
                                        <h2 class="text-2xl font-bold text-gray-900 mb-3">
                                            <?php echo e($block['content']); ?>

                                        </h2>
                                    
                                    <?php elseif($block['type'] === 'text'): ?>
                                        <div class="text-gray-700 prose max-w-none leading-relaxed">
                                            <?php echo nl2br(e($block['content'])); ?>

                                        </div>
                                    
                                    <?php elseif($block['type'] === 'youtube'): ?>
                                        <?php
                                            // Extract YouTube video ID from URL
                                            $videoUrl = $block['content'];
                                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $matches);
                                            $videoId = $matches[1] ?? null;
                                        ?>
                                        
                                        <?php if($videoId): ?>
                                            <div class="video-container my-6">
                                                <h4 class="text-lg font-semibold text-gray-800 mb-3">📹 Video Demonstration</h4>
                                                <div class="relative" style="padding-bottom: 56.25%; height: 0;">
                                                    <iframe 
                                                        src="https://www.youtube.com/embed/<?php echo e($videoId); ?>" 
                                                        frameborder="0" 
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                        allowfullscreen
                                                        class="absolute top-0 left-0 w-full h-full rounded-lg border-4 border-[#F26430]">
                                                    </iframe>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center my-6">
                                                <a href="<?php echo e($block['content']); ?>" target="_blank" class="inline-block">
                                                    <img src="https://placehold.co/500x300/F26430/ffffff?text=Click+to+Watch+Video" 
                                                         alt="Video Placeholder" 
                                                         class="border-4 border-[#F26430] cursor-pointer rounded-lg hover:opacity-90 transition-opacity">
                                                </a>
                                                <p class="text-sm text-gray-600 mt-2">Click image to view on YouTube</p>
                                            </div>
                                        <?php endif; ?>
                                    
                                    <?php elseif($block['type'] === 'image'): ?>
                                        <div class="image-container my-6">
                                            <h4 class="text-lg font-semibold text-gray-800 mb-3">🖼️ Visual Guide</h4>
                                            <div class="border-2 border-[#F26430] p-4 rounded-lg bg-red-50">
                                                <img src="<?php echo e($block['content']); ?>" 
                                                     alt="Lesson Image" 
                                                     class="w-full h-auto border border-gray-400 rounded mb-3">
                                            </div>
                                        </div>
                                    
                                    <?php elseif($block['type'] === 'game'): ?>
                                        <div class="game-container my-6">
                                            <?php
                                                $gameConfig = json_decode($block['content'], true) ?? ['theme' => 'animals', 'gridSize' => 4];
                                            ?>
                                            <div 
                                                data-game-block 
                                                data-game-type="memory"
                                                data-game-config="<?php echo e(json_encode($gameConfig)); ?>">
                                            </div>
                                        </div>
                                    
                                    <?php elseif($block['type'] === 'quiz'): ?>
                                        <div class="quiz-container my-6">
                                            <?php
                                                $quizConfig = json_decode($block['content'], true) ?? ['questions' => []];
                                            ?>
                                            <div 
                                                data-game-block 
                                                data-game-type="quiz"
                                                data-game-config="<?php echo e(json_encode($quizConfig)); ?>">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            
                            <div class="text-gray-700 prose max-w-none">
                                <?php echo nl2br(e($lesson->content)); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="pt-4">
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">Lesson Materials</h3>
                    <?php if($lesson->material_path): ?>
                        <p class="mt-2 text-lg">Downloadable Material: 
                            <a href="<?php echo e(Storage::url($lesson->material_path)); ?>" target="_blank" class="text-[#5FAD56] hover:underline font-bold">
                                <?php echo e(basename($lesson->material_path)); ?>

                            </a>
                        </p>
                    <?php else: ?>
                        <p class="mt-2 text-gray-500">No physical material file available for this lesson.</p>
                    <?php endif; ?>
                </div>


        </div>
    </div>
</div>


<script>
    (function() {
        const lessonId = <?php echo e($lesson->id); ?>;
        const storageKey = `lesson_${lessonId}_progress`;
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');
        
        // Load saved progress
        let maxProgress = parseInt(localStorage.getItem(storageKey) || '0');
        
        function updateProgress(percentage) {
            // Only update if new percentage is higher
            if (percentage > maxProgress) {
                maxProgress = percentage;
                localStorage.setItem(storageKey, maxProgress);
            }
            
            // Update UI
            progressFill.style.width = maxProgress + '%';
            progressText.textContent = maxProgress + '% Complete';
        }
        
        // Initialize with saved progress
        updateProgress(maxProgress);
        
        // Track scroll position
        function handleScroll() {
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Calculate scroll percentage
            const scrollableHeight = documentHeight - windowHeight;
            const scrollPercentage = scrollableHeight > 0 
                ? Math.round((scrollTop / scrollableHeight) * 100)
                : 100;
            
            updateProgress(scrollPercentage);
        }
        
        // Throttle scroll events for performance
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(handleScroll, 100);
        });
        
        // Initial check
        handleScroll();
    })();
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Material\resources\views/lessons/show.blade.php ENDPATH**/ ?>