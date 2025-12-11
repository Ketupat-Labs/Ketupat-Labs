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
                    <?php echo e(__('Welcome back, :name!', ['name' => $user->full_name ?? __('Student')])); ?>

                </h2>
                <p class="text-sm mt-1" style="color: #969696;"><?php echo e(__('Continue your learning journey')); ?></p>
            </div>
            <?php if(($user->points ?? 0) > 0): ?>
                <div class="text-white px-6 py-3 rounded-lg shadow-md"
                    style="background: linear-gradient(to right, #5FAD56, #2454FF);">
                    <div class="text-xs font-medium opacity-90"><?php echo e(__('Total Points')); ?></div>
                    <div class="text-2xl font-bold"><?php echo e($user->points ?? 0); ?> XP</div>
                </div>
            <?php endif; ?>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-8 bg-gradient-to-b from-gray-50 to-white min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Quick Stats Row (3 Columns) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Available Lessons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500"><?php echo e(__('Available Lessons')); ?></p>
                            <p class="text-2xl font-bold mt-1" style="color: #2454FF;">
                                <?php echo e(\App\Models\Lesson::where('is_published', true)->count()); ?>

                            </p>
                        </div>
                        <div class="p-2 rounded-lg bg-blue-50">
                            <svg class="w-6 h-6 text-[#2454FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Completed Lessons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500"><?php echo e(__('Completed Lessons')); ?></p>
                            <p class="text-2xl font-bold mt-1" style="color: #5FAD56;">
                                <?php echo e(\App\Models\Submission::where('user_id', $user->id)->where('status', 'Graded')->count()); ?>

                            </p>
                        </div>
                        <div class="p-2 rounded-lg bg-green-50">
                            <svg class="w-6 h-6 text-[#5FAD56]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- My Submissions -->
                <a href="<?php echo e(route('submission.show')); ?>"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow block group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 group-hover:text-[#FFBA08] transition-colors">
                                <?php echo e(__('My Submissions')); ?></p>
                            <p class="text-2xl font-bold mt-1" style="color: #FFBA08;">
                                <?php echo e(\App\Models\Submission::where('user_id', $user->id)->count()); ?>

                            </p>
                        </div>
                        <div class="p-2 rounded-lg bg-yellow-50">
                            <svg class="w-6 h-6 text-[#FFBA08]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Main Content (3 Columns) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Column 1: Learning Context -->
                <div class="space-y-6">
                    <!-- My Classrooms -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-800"><?php echo e(__('My Classrooms')); ?></h3>
                            <?php if($user->enrolledClassrooms->count() > 3): ?>
                                <a href="#" class="text-sm text-[#2454FF] hover:underline"><?php echo e(__('View All')); ?></a>
                            <?php endif; ?>
                        </div>

                        <?php if($user->enrolledClassrooms->count() > 0): ?>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $user->enrolledClassrooms->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(route('classrooms.show', $classroom)); ?>"
                                        class="block p-4 rounded-lg bg-gray-50 border border-gray-100 hover:bg-white hover:shadow-md hover:border-blue-200 transition-all group">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="text-sm font-bold text-gray-800 group-hover:text-[#2454FF] line-clamp-1">
                                                <?php echo e($classroom->name); ?>

                                            </h4>
                                            <span
                                                class="text-[10px] font-semibold bg-white border border-gray-200 text-gray-500 px-1.5 py-0.5 rounded">
                                                <?php echo e($classroom->year ?? 'N/A'); ?>

                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500"><?php echo e($classroom->subject); ?></p>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-6 text-gray-500 text-sm">
                                <?php echo e(__('Not enrolled in any classes.')); ?>

                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Assignment Timeline -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold mb-6 text-gray-800 flex items-center">
                            <?php echo e(__('Assignment Timeline')); ?>

                        </h3>

                        <div class="space-y-6">

                            <?php $__empty_1 = true; $__currentLoopData = $mixedTimeline ?? $recentAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $isActivity = $item instanceof \App\Models\ActivityAssignment;
                                    $entity = $isActivity ? $item->activity : $item->lesson;
                                ?>
                                <div class="flex group">
                                    <div class="flex flex-col items-center mr-4">
                                        <div
                                            class="w-2.5 h-2.5 <?php echo e($isActivity ? 'bg-purple-600' : 'bg-[#2454FF]'); ?> rounded-full mt-2 group-hover:ring-2 <?php echo e($isActivity ? 'ring-purple-100' : 'ring-blue-100'); ?> transition-all">
                                        </div>
                                        <div class="w-px h-full bg-gray-100 my-1 group-last:hidden"></div>
                                    </div>
                                    <div class="flex-1 pb-6 mb-2 border-b border-gray-50 last:border-0 last:pb-0">
                                        <div class="flex justify-between items-start mb-1">
                                            <span
                                                class="text-[10px] font-bold <?php echo e($isActivity ? 'text-purple-600 bg-purple-50' : 'text-[#2454FF] bg-blue-50'); ?> uppercase tracking-wider px-1.5 py-0.5 rounded">
                                                <?php echo e($item->classroom->subject); ?>

                                            </span>
                                            <span class="text-xs text-gray-400">
                                                <?php echo e($item->assigned_at ? \Carbon\Carbon::parse($item->assigned_at)->diffForHumans() : __('Recently')); ?>

                                            </span>
                                        </div>
                                        <h4 class="text-sm font-bold text-gray-800 hover:text-[#2454FF] transition mt-1">
                                            <?php if(!$isActivity): ?>
                                                <a href="<?php echo e(route('lesson.show', $entity)); ?>"><?php echo e($entity->title); ?></a>
                                            <?php else: ?>
                                                <a href="<?php echo e(route('activities.show', $entity)); ?>"><?php echo e($entity->title); ?></a>
                                                <span class="text-xs font-normal text-gray-500 ml-1">(<?php echo e($entity->suggested_duration); ?>)</span>
                                            <?php endif; ?>
                                        </h4>
                                        
                                        <?php if(!$isActivity): ?>
                                            <a href="<?php echo e(route('lesson.show', $entity)); ?>"
                                                class="text-xs font-semibold text-gray-500 hover:text-[#2454FF] flex items-center mt-2">
                                                <?php echo e(__('Start')); ?> <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        <?php else: ?>
                                             <div class="flex flex-col mt-1">
                                                <span class="text-xs text-gray-500"><?php echo e($entity->type); ?></span>
                                                <?php if($item->due_date): ?>
                                                    <span class="text-xs text-red-500 font-medium">Due: <?php echo e(\Carbon\Carbon::parse($item->due_date)->format('d M Y')); ?></span>
                                                <?php endif; ?>
                                             </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center py-8 text-gray-400 text-sm">
                                    <?php echo e(__('No immediate assignments.')); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Progress & Actions -->
                <div class="space-y-6">
                    <!-- Latest Grade -->
                    <?php if(isset($recentFeedback)): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-2 opacity-5">
                                <svg class="w-16 h-16 text-[#5FAD56]" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd"
                                        d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                <?php echo e(__('Latest Grade')); ?></h4>
                            <div class="relative z-10">
                                <h3 class="font-bold text-gray-800 text-sm mb-2 line-clamp-1">
                                    <?php echo e($recentFeedback->lesson->title ?? 'Lesson'); ?>

                                </h3>
                                <div class="flex items-center space-x-2 mb-3">
                                    <span class="text-3xl font-extrabold text-[#5FAD56]"><?php echo e($recentFeedback->grade); ?></span>
                                    <span class="text-xs text-gray-400">/ 100</span>
                                </div>
                                <?php if($recentFeedback->feedback): ?>
                                    <p class="text-xs text-gray-500 italic mb-3 line-clamp-2">"<?php echo e($recentFeedback->feedback); ?>"
                                    </p>
                                <?php endif; ?>
                                <a href="<?php echo e(route('submission.show')); ?>"
                                    class="text-xs font-medium text-[#2454FF] hover:underline"><?php echo e(__('View Feedback')); ?></a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                <?php echo e(__('Latest Grade')); ?></h4>
                            <p class="text-gray-500 text-sm"><?php echo e(__('No grades yet.')); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Menu -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <h4 class="text-sm font-bold text-gray-800 px-5 py-3 bg-gray-50 border-b border-gray-100">
                            <?php echo e(__('Quick Menu')); ?></h4>
                        <div class="divide-y divide-gray-100">
                            <a href="<?php echo e(route('lesson.index')); ?>"
                                class="flex items-center px-5 py-3 hover:bg-gray-50 transition group">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center mr-3 group-hover:bg-[#2454FF] transition-colors">
                                    <svg class="w-4 h-4 text-[#2454FF] group-hover:text-white transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                        </path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700"><?php echo e(__('Browse Lessons')); ?></span>
                            </a>
                            <a href="<?php echo e(route('quiz.show')); ?>"
                                class="flex items-center px-5 py-3 hover:bg-gray-50 transition group">
                                <div
                                    class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center mr-3 group-hover:bg-[#F26430] transition-colors">
                                    <svg class="w-4 h-4 text-[#F26430] group-hover:text-white transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                        </path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700"><?php echo e(__('Take Quiz')); ?></span>
                            </a>
                            <a href="<?php echo e(route('submission.show')); ?>"
                                class="flex items-center px-5 py-3 hover:bg-gray-50 transition group">
                                <div
                                    class="w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center mr-3 group-hover:bg-[#FFBA08] transition-colors">
                                    <svg class="w-4 h-4 text-[#FFBA08] group-hover:text-white transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                        </path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700"><?php echo e(__('My Submissions')); ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Discovery -->
                <div class="space-y-6">
                    <!-- Public Lessons -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-800"><?php echo e(__('Public Lessons')); ?></h4>
                            <a href="<?php echo e(route('lesson.index')); ?>"
                                class="text-xs font-semibold text-[#2454FF] hover:underline"><?php echo e(__('View All')); ?></a>
                        </div>
                        <div class="space-y-3">
                            @php
                            $publicLessons = \App\Models\Lesson::where('is_published', true)->latest()->take(6)->get();

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
<?php endif; ?><?php /**PATH /Users/raudhahmaszamanie/Downloads/Ketupat-Labs-fix-REALMAIN/resources/views/dashboard/student.blade.php ENDPATH**/ ?>