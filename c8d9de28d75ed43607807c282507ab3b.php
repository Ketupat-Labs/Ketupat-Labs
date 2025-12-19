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
            <?php echo e(__('AI Generator')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo e(__('AI Content Generator')); ?></h1>
                        <p class="text-gray-600"><?php echo e(__('Generate educational slides and quizzes using AI')); ?></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Generate Slides Card -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="bg-blue-500 rounded-lg p-3 mr-4">
                                    <i class="fas fa-presentation text-white text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900"><?php echo e(__('Generate Slides')); ?></h3>
                            </div>
                            <p class="text-gray-600 mb-4"><?php echo e(__('Create educational presentation slides on any topic using AI.')); ?></p>
                            <a href="<?php echo e(route('ai-generator.slides')); ?>" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-magic mr-2"></i>
                                <?php echo e(__('Generate Slides')); ?>

                            </a>
                        </div>

                        <!-- Generate Quiz Card -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="bg-green-500 rounded-lg p-3 mr-4">
                                    <i class="fas fa-question-circle text-white text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900"><?php echo e(__('Generate Quiz')); ?></h3>
                            </div>
                            <p class="text-gray-600 mb-4"><?php echo e(__('Create quiz questions with multiple choice or true/false options.')); ?></p>
                            <a href="<?php echo e(route('ai-generator.quiz')); ?>" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-magic mr-2"></i>
                                <?php echo e(__('Generate Quiz')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

<?php /**PATH C:\Users\HP\OneDrive\文档\GitHub\Ketupat-Labs\resources\views/ai-generator/index.blade.php ENDPATH**/ ?>