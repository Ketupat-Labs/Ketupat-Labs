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
        <div class="mb-6 px-4 sm:px-0">
            <h1 class="text-3xl font-extrabold text-[#2454FF] tracking-tight">Available Lessons</h1>
            <p class="text-gray-600 mt-2">Browse and access all published lessons</p>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php if($item->item_type === 'activity'): ?>
                             
                            <div class="border border-purple-200 bg-purple-50 rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-xl font-semibold text-purple-700"><?php echo e($item->title); ?></h3>
                                    <span class="bg-purple-200 text-purple-800 text-xs font-bold px-2 py-1 rounded uppercase">Activity</span>
                                </div>
                                <p class="text-gray-600 text-sm mb-2 font-medium"><?php echo e($item->type); ?></p>
                                <p class="text-gray-500 text-sm mb-4">Duration: <?php echo e($item->suggested_duration); ?></p>
                                <?php if($item->due_date): ?>
                                     <p class="text-red-500 text-sm font-bold mb-4">Due: <?php echo e(\Carbon\Carbon::parse($item->due_date)->format('M d, Y')); ?></p>
                                <?php endif; ?>
                                <a href="<?php echo e(route('activities.show', $item->id)); ?>" 
                                   class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150">
                                    View Activity
                                </a>
                            </div>
                        <?php else: ?>
                            
                            <div class="border border-gray-200 bg-white rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <h3 class="text-xl font-semibold text-[#2454FF] mb-2"><?php echo e($item->title); ?></h3>
                                <p class="text-gray-600 text-sm mb-2">Topic: <?php echo e($item->topic); ?></p>
                                <?php if($item->duration): ?>
                                <p class="text-gray-500 text-sm mb-4">Duration: <?php echo e($item->duration); ?> mins</p>
                                <?php endif; ?>
                                <a href="<?php echo e(route('lesson.show', $item->id)); ?>" 
                                   class="inline-block bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150">
                                    View Lesson
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500 text-lg">No content available at the moment.</p>
                    </div>
                    <?php endif; ?>
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

<?php /**PATH /Users/raudhahmaszamanie/Downloads/Ketupat-Labs-fix-REALMAIN/resources/views/lessons/student-index.blade.php ENDPATH**/ ?>