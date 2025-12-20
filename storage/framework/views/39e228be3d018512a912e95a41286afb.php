
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
                    <?php echo e(__('Welcome back, :name!', ['name' => $user->full_name ?? __('Teacher')])); ?>

                </h2>
                <p class="text-sm mt-1" style="color: #969696;"><?php echo e(__('Manage your classes and lessons')); ?></p>
            </div>
            <div class="flex space-x-4">
                <a href="<?php echo e(route('classrooms.create')); ?>"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <?php echo e(__('Create Class')); ?>

                </a>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-8 bg-gradient-to-b from-gray-50 to-white min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <!-- Quick Stats Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-compuplay-gray"><?php echo e(__('Published Lessons')); ?></p>
                            <p class="text-3xl font-bold mt-2" style="color: #2454FF;">
                                <?php echo e(\App\Models\Lesson::where('is_published', true)->where('teacher_id', $user->id)->count()); ?>

                            </p>
                        </div>
                        <div class="p-3 rounded-lg" style="background-color: rgba(36, 84, 255, 0.1);">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                style="color: #2454FF;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-base font-medium text-compuplay-gray"><?php echo e(__('Pending Grading')); ?></p>
                            <p class="text-3xl font-bold mt-2" style="color: #F26430;">
                                <?php echo e(\App\Models\Submission::where('status', 'Submitted - Awaiting Grade')->count()); ?></p>
                        </div>
                        <div class="p-3 rounded-lg" style="background-color: rgba(242, 100, 48, 0.1);">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                style="color: #F26430;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- View Submissions Card -->
                <a href="<?php echo e(route('submission.index')); ?>"
                    class="group bg-white rounded-xl shadow-sm border-2 border-transparent hover:shadow-lg transition-all duration-300 overflow-hidden"
                    onmouseover="this.style.borderColor='#F26430'"
                    onmouseout="this.style.borderColor='transparent'">
                    <div class="p-6 text-white"
                        style="background: linear-gradient(to bottom right, #F26430, #c44d26);">
                        <div class="flex items-center justify-between mb-4">
                            <svg class="w-12 h-12 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            <svg class="w-6 h-6 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold mb-2"><?php echo e(__('Review Submissions')); ?></h4>
                        <p class="text-sm opacity-90"><?php echo e(__('Grade student assignments')); ?></p>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center text-sm" style="color: #969696;">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                            <span><?php echo e(\App\Models\Submission::where('status', 'Submitted - Awaiting Grade')->count()); ?>

                                <?php echo e(__('pending')); ?></span>
                        </div>
                    </div>
                </a>

                <!-- Assign Lessons Card -->
                <a href="<?php echo e(route('assignments.create')); ?>"
                    class="group bg-white rounded-xl shadow-sm border-2 border-transparent hover:shadow-lg transition-all duration-300 overflow-hidden"
                    onmouseover="this.style.borderColor='#2454FF'"
                    onmouseout="this.style.borderColor='transparent'">
                    <div class="p-6 text-white"
                        style="background: linear-gradient(to bottom right, #2454FF, #1a3fcc);">
                        <div class="flex items-center justify-between mb-4">
                            <svg class="w-12 h-12 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            <svg class="w-6 h-6 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold mb-2"><?php echo e(__('Assign Lessons')); ?></h4>
                        <p class="text-sm opacity-90"><?php echo e(__('Assign lessons to students')); ?></p>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center text-sm" style="color: #969696;">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                            <span><?php echo e(__('Manage assignments')); ?></span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- My Classrooms Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold" style="color: #3E3E3E;"><?php echo e(__('My Classrooms')); ?></h3>
                    <a href="<?php echo e(route('classrooms.create')); ?>" class="text-sm font-semibold text-[#2454FF] hover:underline">
                        + <?php echo e(__('Create New Class')); ?>

                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php $__empty_1 = true; $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('classrooms.show', $classroom)); ?>" class="block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all group overflow-hidden">
                            <div class="h-2 bg-gradient-to-r from-[#2454FF] to-[#1a3fcc]"></div>
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-lg font-bold text-gray-800 group-hover:text-[#2454FF] transition"><?php echo e($classroom->name); ?></h4>
                                    <span class="bg-blue-100 text-[#2454FF] text-xs font-bold px-2 py-1 rounded-full">
                                        <?php echo e($classroom->students_count); ?> <?php echo e(__('Students')); ?>

                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mb-4"><?php echo e($classroom->subject); ?> &bull; <?php echo e($classroom->year); ?></p>
                                <div class="flex items-center text-sm font-medium text-[#5FAD56]">
                                    <span><?php echo e(__('Go to Classroom')); ?></span>
                                    <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p class="text-gray-500 mb-4"><?php echo e(__('You haven\'t created any classrooms yet.')); ?></p>
                            <a href="<?php echo e(route('classrooms.create')); ?>" class="inline-block bg-[#2454FF] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                <?php echo e(__('Create Your First Class')); ?>

                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Lessons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold" style="color: #3E3E3E;"><?php echo e(__('My Recent Lessons')); ?></h3>
                        <a href="<?php echo e(route('lessons.index')); ?>" class="text-sm hover:underline font-medium"
                            style="color: #2454FF;"><?php echo e(__('View all')); ?></a>
                    </div>
                    <div class="space-y-4">
                        <?php
                            $recentLessons = \App\Models\Lesson::where('teacher_id', $user->id)->latest()->take(3)->get();
                        ?>
                        <?php $__empty_1 = true; $__currentLoopData = $recentLessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <a href="<?php echo e(route('lessons.show', $lesson->id)); ?>"
                                class="flex items-center p-4 bg-gray-50 rounded-lg transition-colors group"
                                style="background-color: #f9fafb;"
                                onmouseover="this.style.backgroundColor='rgba(36, 84, 255, 0.05)'"
                                onmouseout="this.style.backgroundColor='#f9fafb'">
                                <div class="flex-shrink-0 p-2 rounded-lg mr-4"
                                    style="background-color: rgba(36, 84, 255, 0.1);">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        style="color: #2454FF;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold transition-colors" style="color: #3E3E3E;">
                                        <?php echo e($lesson->title); ?></p>
                                    <p class="text-xs mt-1" style="color: #969696;"><?php echo e($lesson->topic); ?> •
                                        <?php echo e($lesson->is_published ? __('Published') : __('Draft')); ?></p>
                                </div>
                                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="color: #969696;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-8" style="color: #969696;">
                                <p><?php echo e(__('No lessons created yet')); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold mb-4" style="color: #3E3E3E;"><?php echo e(__('Quick Actions')); ?></h3>
                    <div class="space-y-3">
                        <a href="<?php echo e(route('lessons.create')); ?>"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #5FAD56, #4a8d45);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="font-semibold"><?php echo e(__('Create New Lesson')); ?></span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <a href="<?php echo e(route('submission.index')); ?>"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #F26430, #c44d26);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                <span class="font-semibold"><?php echo e(__('Review Submissions')); ?></span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <a href="<?php echo e(route('assignments.create')); ?>"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #2454FF, #1a3fcc);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="font-semibold"><?php echo e(__('Assign Lessons')); ?></span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <a href="<?php echo e(route('activities.index')); ?>"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #F26430, #FFBA08);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-semibold"><?php echo e(__('Assign Activity')); ?></span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <a href="<?php echo e(route('progress.index')); ?>"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #8B5CF6, #7C3AED);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                <span class="font-semibold"><?php echo e(__('Monitor Progress')); ?></span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Material\resources\views/dashboard/teacher.blade.php ENDPATH**/ ?>