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
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl leading-tight" style="color: #3E3E3E;">
                    <?php echo e(__('Course Catalog')); ?>

                </h2>
                <p class="text-sm mt-1" style="color: #969696;">Explore and enroll in extra learning materials.</p>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <?php if(session('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6"
                    role="alert">
                    <span class="block sm:inline"><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo e(session('error')); ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                        <!-- Top Border Color -->
                        <div
                            class="absolute top-0 left-0 w-full h-1 <?php echo e($lesson->is_mandatory ? 'bg-red-500' : 'bg-blue-500'); ?>">
                        </div>

                        <!-- Status Badge -->
                        <div class="absolute top-4 right-4">
                            <?php if($lesson->enrolled): ?>
                                <span class="px-2 py-1 text-xs font-bold text-white bg-green-500 rounded-full">Enrolled</span>
                            <?php elseif($lesson->is_mandatory): ?>
                                <span class="px-2 py-1 text-xs font-bold text-white bg-gray-500 rounded-full">Mandatory</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-bold text-white bg-blue-500 rounded-full">Optional</span>
                            <?php endif; ?>
                        </div>

                        <h3 class="text-xl font-bold text-gray-800 mt-4 mb-2"><?php echo e($lesson->title); ?></h3>
                        <p class="text-sm text-gray-500 mb-4">Topic: <?php echo e($lesson->topic); ?></p>

                        <div class="flex items-center text-sm font-semibold text-green-600 mb-6">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?php echo e($lesson->duration ?? 'N/A'); ?> mins
                        </div>

                        <form method="POST" action="<?php echo e(route('enrollment.store')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="lesson_id" value="<?php echo e($lesson->id); ?>">

                            <?php if($lesson->enrolled): ?>
                                <button type="button"
                                    class="w-full py-2 px-4 bg-gray-300 text-gray-600 font-bold rounded cursor-not-allowed"
                                    disabled>
                                    Already Enrolled
                                </button>
                            <?php else: ?>
                                <button type="submit"
                                    class="w-full py-2 px-4 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 transition-colors">
                                    Enroll Now
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php endif; ?><?php /**PATH /Users/raudhahmaszamanie/Downloads/Ketupat-Labs-fix-REALMAIN/resources/views/enrollment/index.blade.php ENDPATH**/ ?>