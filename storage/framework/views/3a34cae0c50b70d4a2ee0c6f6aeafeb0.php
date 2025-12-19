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
            <!-- Back Button -->
            <a href="<?php echo e(route('classrooms.index')); ?>" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Senarai Kelas
            </a>

            
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
                    <div class="flex gap-3">
                        <?php if($user->role === 'teacher'): ?>
                            <a href="<?php echo e(route('lessons.create')); ?>"
                                class="inline-flex items-center px-4 py-2 bg-[#F26430] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Create New Lesson
                            </a>
                        <?php endif; ?>
                        <?php if($forum): ?>
                            <a href="<?php echo e(route('forum.detail', $forum->id)); ?>"
                                class="inline-flex items-center px-4 py-2 bg-[#2454FF] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                View Forum
                            </a>
                        <?php elseif($user->role === 'teacher'): ?>
                            <button onclick="createClassroomForum(<?php echo e($classroom->id); ?>)"
                                class="inline-flex items-center px-4 py-2 bg-[#2454FF] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Create Forum
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                
                <div class="lg:col-span-1 space-y-6">

                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-[#2454FF] mb-4 border-b border-gray-100 pb-2">Instructors</h3>
                        <a href="<?php echo e(route('profile.show', $classroom->teacher->id)); ?>" class="flex items-center space-x-3 hover:opacity-80 transition-opacity cursor-pointer">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-[#2454FF] font-bold overflow-hidden flex-shrink-0">
                                <?php if($classroom->teacher->avatar_url): ?>
                                    <img src="<?php echo e(asset($classroom->teacher->avatar_url)); ?>" 
                                         alt="<?php echo e($classroom->teacher->full_name); ?>" 
                                         class="h-full w-full object-cover"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;">
                                        <?php echo e(strtoupper(substr($classroom->teacher->full_name, 0, 1))); ?>

                                    </div>
                                <?php else: ?>
                                    <?php echo e(strtoupper(substr($classroom->teacher->full_name, 0, 1))); ?>

                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium hover:text-[#2454FF] transition-colors"><?php echo e($classroom->teacher->full_name); ?></p>
                                <p class="text-gray-500 text-xs">Lead Teacher</p>
                            </div>
                        </a>
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
                                    <div x-data="{ 
                                            open: false, 
                                            search: '', 
                                            selectedId: '', 
                                            selectedName: '' 
                                        }" 
                                        class="relative flex-1"
                                        @click.away="open = false">
                                        
                                        <input type="hidden" name="student_id" x-model="selectedId" required>

                                        <button type="button" 
                                            @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                                            class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <span class="block truncate" x-text="selectedName || 'Select Student...'"></span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>

                                        <div x-show="open" 
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                            style="display: none;">
                                            
                                            <div class="px-2 py-2 sticky top-0 bg-white border-b z-20">
                                                <input x-ref="searchInput" x-model="search" type="text" placeholder="Search student name..." class="w-full border-gray-300 rounded-md text-xs p-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>

                                            <ul class="pt-1">
                                                <?php $__currentLoopData = $availableStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 hover:bg-indigo-600 hover:text-white group"
                                                        x-show="'<?php echo e(strtolower($student->full_name)); ?>'.includes(search.toLowerCase())"
                                                        @click="selectedId = '<?php echo e($student->id); ?>'; selectedName = '<?php echo e($student->full_name); ?>'; open = false; search = ''">
                                                        <span class="block truncate font-normal group-hover:font-semibold">
                                                            <?php echo e($student->full_name); ?>

                                                        </span>
                                                        <span x-show="selectedId === '<?php echo e($student->id); ?>'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 group-hover:text-white">
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <li x-show="search !== '' && $el.parentNode.querySelectorAll('li[x-show]:not([style*=\'display: none\'])').length === 0" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 italic">
                                                    No students found.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
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
                                    <a href="<?php echo e(route('profile.show', $student->id)); ?>" class="flex items-center space-x-3 flex-1 hover:opacity-80 transition-opacity cursor-pointer">
                                        <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs ring-2 ring-transparent group-hover:ring-purple-200 transition overflow-hidden flex-shrink-0">
                                            <?php if($student->avatar_url): ?>
                                                <img src="<?php echo e(asset($student->avatar_url)); ?>" 
                                                     alt="<?php echo e($student->full_name); ?>" 
                                                     class="h-full w-full object-cover"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;">
                                                    <?php echo e(strtoupper(substr($student->full_name, 0, 1))); ?>

                                                </div>
                                            <?php else: ?>
                                                <?php echo e(strtoupper(substr($student->full_name, 0, 1))); ?>

                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm text-gray-700 font-medium hover:text-[#5FAD56] transition-colors"><?php echo e($student->full_name); ?></span>
                                    </a>
                                    <?php if($user->role === 'teacher'): ?>
                                        <form method="POST"
                                            action="<?php echo e(route('classrooms.students.remove', [$classroom, $student->id])); ?>"
                                            onsubmit="return confirm('Remove <?php echo e($student->full_name); ?> from this class?');"
                                            class="ml-2"
                                            onclick="event.stopPropagation();">
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

                
                <div class="lg:col-span-2" x-data="{ 
                    search: '',
                    fuzzyMatch(text, query) {
                        text = (text || '').toLowerCase();
                        query = (query || '').toLowerCase().trim();
                        if (!query) return true;
                        let i = 0, n = -1, l;
                        for (; l = query[i++];) {
                            if (!~(n = text.indexOf(l, n + 1))) return false;
                        }
                        return true;
                    }
                }">
                    
                    
                    <div class="mb-6">
                        <div class="relative">
                            <input type="text" x-model="search" placeholder="Cari pelajaran atau aktiviti..." 
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-100 transition shadow-sm text-gray-700">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-[600px]">
                            <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                                <h3 class="font-bold text-gray-800 flex items-center">
                                    <span class="bg-[#2454FF] w-2 h-6 rounded-full mr-2"></span>
                                    Pelajaran
                                </h3>
                            </div>
                            <div class="p-4 overflow-y-auto flex-1 space-y-4">
                                <?php $__empty_1 = true; $__currentLoopData = $classroom->lessons->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="bg-white border border-gray-100 rounded-lg p-4 shadow-sm hover:shadow-md transition duration-200"
                                         x-show="fuzzyMatch('<?php echo e($lesson->title); ?>', search)"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-100 transform scale-100">
                                        
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-bold text-[#2454FF] hover:underline leading-snug">
                                                <a href="<?php echo e(route('lesson.show', $lesson)); ?>"><?php echo e($lesson->title); ?></a>
                                            </h4>
                                            
                                            
                                            <?php if($user->role === 'student' && $lesson->enrollments->where('user_id', $user->id)->first()): ?>
                                                <?php
                                                    $status = $lesson->enrollments->where('user_id', $user->id)->first()->status;
                                                    $statusColors = [
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                        'not_started' => 'bg-gray-100 text-gray-600',
                                                    ];
                                                ?>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full <?php echo e($statusColors[$status] ?? 'bg-gray-100'); ?>">
                                                    <?php echo e(str_replace('_', ' ', ucfirst($status))); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <p class="text-xs text-gray-500 mb-3"><?php echo e($lesson->topic); ?></p>
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-3"><?php echo e($lesson->content); ?></p>

                                        <div class="grid grid-cols-1 gap-2">
                                            <a href="<?php echo e(route('lesson.show', $lesson)); ?>" 
                                               class="text-center text-xs font-bold text-[#2454FF] bg-blue-50 py-2 rounded hover:bg-blue-100 transition">
                                                Buka Pelajaran
                                            </a>
                                            <?php if($user->role === 'teacher'): ?>
                                                <a href="<?php echo e(route('submission.index', ['lesson_id' => $lesson->id, 'classroom_id' => $classroom->id])); ?>"
                                                   class="text-center text-xs font-bold text-[#5FAD56] bg-green-50 py-2 rounded hover:bg-green-100 transition">
                                                   Semak Tugasan (<?php echo e($lesson->submissions->count()); ?>)
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <p class="text-center text-gray-400 text-sm italic py-4">Tiada pelajaran.</p>
                                <?php endif; ?>
                                <p x-show="search !== '' && $el.parentNode.querySelectorAll('div[x-show]:not([style*=\'display: none\'])').length === 0" 
                                   class="text-center text-gray-400 text-sm italic py-4" style="display: none;">
                                    Tiada pelajaran dijumpai.
                                </p>
                            </div>
                        </div>

                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-[600px]">
                            <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                                <h3 class="font-bold text-gray-800 flex items-center">
                                    <span class="bg-purple-600 w-2 h-6 rounded-full mr-2"></span>
                                    Aktiviti
                                </h3>
                            </div>
                            <div class="p-4 overflow-y-auto flex-1 space-y-4">
                                <?php $__empty_1 = true; $__currentLoopData = $classroom->activityAssignments->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="bg-white border border-gray-100 rounded-lg p-4 shadow-sm hover:shadow-md transition duration-200"
                                         x-show="fuzzyMatch('<?php echo e($assignment->activity->title); ?>', search)"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-100 transform scale-100">
                                        
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-bold text-purple-600 hover:underline leading-snug">
                                                <a href="<?php echo e(route('activities.show', $assignment->activity)); ?>"><?php echo e($assignment->activity->title); ?></a>
                                            </h4>
                                            <span class="text-[10px] font-bold bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                                <?php echo e($assignment->activity->type); ?>

                                            </span>
                                        </div>

                                        <?php if($assignment->due_date): ?>
                                            <p class="text-xs text-red-500 font-bold mb-2">
                                                Tamat: <?php echo e(\Carbon\Carbon::parse($assignment->due_date)->format('d M Y')); ?>

                                            </p>
                                        <?php endif; ?>

                                        <p class="text-sm text-gray-600 line-clamp-2 mb-3"><?php echo e($assignment->activity->description); ?></p>

                                        <a href="<?php echo e(route('activities.show', $assignment->activity)); ?>" 
                                           class="block text-center text-xs font-bold text-purple-600 bg-purple-50 py-2 rounded hover:bg-purple-100 transition">
                                            Lihat Aktiviti
                                        </a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <p class="text-center text-gray-400 text-sm italic py-4">Tiada aktiviti.</p>
                                <?php endif; ?>
                                <p x-show="search !== '' && $el.parentNode.querySelectorAll('div[x-show]:not([style*=\'display: none\'])').length === 0" 
                                   class="text-center text-gray-400 text-sm italic py-4" style="display: none;">
                                    Tiada aktiviti dijumpai.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        async function createClassroomForum(classroomId) {
            if (!confirm('Create a forum for this classroom? The forum will be visible only to class members.')) {
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch('/api/classroom/' + classroomId + '/create-forum', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'include',
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.status === 200) {
                    // Redirect to forum detail page
                    window.location.href = '/forum/' + data.data.forum_id;
                } else {
                    alert(data.message || 'Failed to create forum');
                }
            } catch (error) {
                console.error('Error creating forum:', error);
                alert('Failed to create forum. Please try again.');
            }
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Material\resources\views/classrooms/show.blade.php ENDPATH**/ ?>