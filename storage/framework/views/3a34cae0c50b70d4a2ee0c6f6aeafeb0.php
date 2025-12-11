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
            <?php echo e($classroom->name); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            
            <?php if(session('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6"
                    role="alert">
                    <span class="block sm:inline"><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8 border border-gray-200">
                <div class="bg-gradient-to-r from-[#2454FF] to-[#1a3fcc] p-8 text-white">
                    <h1 class="text-4xl font-bold mb-2"><?php echo e($classroom->name); ?></h1>
                    <div class="flex items-center space-x-4 opacity-90">
                        <span class="text-lg"><?php echo e($classroom->subject); ?></span>
                        <?php if($classroom->year): ?>
                            <span>&bull;</span>
                            <span class="text-lg"><?php echo e($classroom->year); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Class Code: <span
                            class="font-mono font-bold text-gray-700"><?php echo e($classroom->id); ?>-<?php echo e(Str::upper(Str::random(4))); ?></span>
                        (Simulated)
                    </div>
                    <?php if($user->role === 'teacher'): ?>
                        <a href="<?php echo e(route('lessons.create')); ?>"
                            class="inline-flex items-center px-4 py-2 bg-[#F26430] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Create New Lesson
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                
                <div class="lg:col-span-1 space-y-6">

                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-[#2454FF] mb-4 border-b border-gray-100 pb-2">Instructors</h3>
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-[#2454FF] font-bold">
                                <?php echo e(substr($classroom->teacher->full_name, 0, 1)); ?>

                            </div>
                            <div>
                                <p class="text-gray-900 font-medium"><?php echo e($classroom->teacher->full_name); ?></p>
                                <p class="text-gray-500 text-xs">Lead Teacher</p>
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
                            <h3 class="text-lg font-bold text-[#5FAD56]">Class Roster</h3>
                            <span
                                class="text-xs font-semibold bg-green-100 text-green-800 px-2 py-1 rounded-full"><?php echo e($classroom->students->count()); ?>

                                Students</span>
                        </div>

                        
                        <?php if($user->role === 'teacher'): ?>
                            <div class="mb-6 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <p class="text-xs font-bold text-gray-500 mb-2 uppercase">Add Student</p>
                                <form method="POST" action="<?php echo e(route('classrooms.students.add', $classroom)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <div class="flex space-x-2">
                                        <select name="student_id" required
                                            class="flex-1 text-sm border-gray-300 rounded-md focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="">Select...</option>
                                            <?php $__currentLoopData = $availableStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($student->id); ?>"><?php echo e($student->full_name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <button type="submit"
                                            class="p-2 bg-[#5FAD56] text-white rounded-md hover:bg-green-700 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                        
                        <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                            <?php $__empty_1 = true; $__currentLoopData = $classroom->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div
                                    class="flex items-center justify-between group p-2 hover:bg-gray-50 rounded transition">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs ring-2 ring-transparent group-hover:ring-purple-200 transition">
                                            <?php echo e(substr($student->full_name, 0, 1)); ?>

                                        </div>
                                        <span class="text-sm text-gray-700 font-medium"><?php echo e($student->full_name); ?></span>
                                    </div>
                                    <?php if($user->role === 'teacher'): ?>
                                        <form method="POST"
                                            action="<?php echo e(route('classrooms.students.remove', [$classroom, $student->id])); ?>"
                                            onsubmit="return confirm('Remove <?php echo e($student->full_name); ?> from this class?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-gray-300 hover:text-red-500 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center py-4">
                                    <p class="text-gray-400 text-sm italic">No students enrolled yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[500px]">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <span class="bg-[#2454FF] w-2 h-8 rounded-full mr-3"></span>
                            Course Timeline
                        </h3>

                        <div class="space-y-6">
                            <?php $__empty_1 = true; $__currentLoopData = $classroom->lessons->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="relative pl-8 border-l-2 border-gray-200 pb-6 last:pb-0 last:border-0">
                                    
                                    <div
                                        class="absolute -left-[9px] top-0 bg-white border-4 border-[#2454FF] h-4 w-4 rounded-full">
                                    </div>

                                    <div
                                        class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:shadow-md transition duration-200">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-lg font-bold text-gray-800 hover:text-[#2454FF] transition">
                                                    <a href="<?php echo e(route('lesson.show', $lesson)); ?>"><?php echo e($lesson->title); ?></a>
                                                </h4>
                                                <p class="text-sm text-gray-500 mb-2"><?php echo e($lesson->topic); ?> &bull; Posted
                                                    <?php echo e($lesson->created_at ? $lesson->created_at->format('M d, Y') : 'N/A'); ?></p>
                                                <p class="text-gray-600 text-sm mb-4">
                                                    <?php echo e(Str::limit($lesson->content, 120)); ?></p>
                                            </div>
                                            
                                            
                                            <?php if($user->role === 'teacher'): ?>
                                                <div class="bg-blue-100 text-[#2454FF] text-xs font-bold px-2 py-1 rounded whitespace-nowrap ml-2">
                                                    <?php echo e($lesson->submissions->whereIn('user_id', $classroom->students->pluck('id'))->count()); ?>

                                                    / <?php echo e($classroom->students->count()); ?> Turned In
                                                </div>
                                            <?php else: ?>
                                                <?php
                                                    $enrollment = $lesson->enrollments->first();
                                                    $status = $enrollment ? $enrollment->status : 'not_started';
                                                    $statusColors = [
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                        'not_started' => 'bg-gray-100 text-gray-600',
                                                    ];
                                                    $statusLabel = [
                                                        'completed' => 'Completed',
                                                        'in_progress' => 'In Progress',
                                                        'not_started' => 'Not Started',
                                                    ];
                                                ?>
                                                <div class="<?php echo e($statusColors[$status] ?? 'bg-gray-100'); ?> text-xs font-bold px-2 py-1 rounded whitespace-nowrap ml-2 uppercase tracking-wide">
                                                    <?php echo e($statusLabel[$status] ?? 'Not Started'); ?>

                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex space-x-3 mt-2">
                                            <a href="<?php echo e(route('lesson.show', $lesson)); ?>"
                                                class="text-sm font-semibold text-[#2454FF] hover:underline">
                                                <?php echo e($user->role === 'student' && ($lesson->enrollments->first()->status ?? '') === 'completed' ? 'Review Lesson' : 'Open Lesson'); ?>

                                            </a>
                                            <?php if($user->role === 'teacher'): ?>
                                                <span class="text-gray-300">|</span>
                                                <a href="<?php echo e(route('submission.index', ['lesson_id' => $lesson->id, 'classroom_id' => $classroom->id])); ?>"
                                                    class="text-sm font-semibold text-[#5FAD56] hover:underline">
                                                    Grade Submissions
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No lessons yet</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new lesson.</p>
                                </div>
                            <?php endif; ?>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Material\resources\views/classrooms/show.blade.php ENDPATH**/ ?>