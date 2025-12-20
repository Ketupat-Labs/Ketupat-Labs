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
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Jadual Prestasi Pelajar</h2>
                <p class="text-gray-600">Pantau dan analisis prestasi pelajar merentasi pelajaran</p>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="<?php echo e(route('performance.index')); ?>" id="filter_form"
                        class="flex flex-wrap items-center gap-6">

                        <!-- Class Filter -->
                        <!-- Class Filter -->
                        <?php if(auth()->user()->role === 'teacher'): ?>
                            <div class="flex flex-col gap-1">
                                <label for="class_id" class="text-sm font-medium text-gray-700">Kelas</label>
                                <select name="class_id" id="class_id" onchange="this.form.submit()"
                                    class="block pl-3 pr-10 py-2 text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md min-w-[200px] shadow-sm">
                                    <option value="" disabled <?php echo e(!$selectedClass ? 'selected' : ''); ?>>Sila Pilih Kelas
                                    </option>
                                    <?php $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($classroom->id); ?>" class="text-gray-900 bg-white" <?php echo e(($selectedClass && $selectedClass->id == $classroom->id) ? 'selected' : ''); ?>>
                                            <?php echo e($classroom->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Unified Item Filter (Searchable) -->
                        <div class="flex flex-col gap-1 w-full md:w-auto" 
                             x-data="{ 
                                open: false, 
                                search: '', 
                                selectedIdentifier: '<?php echo e($selectedFilter); ?>',
                                selectedName: '<?php echo e($selectedFilter === 'all' ? 'Semua Pelajaran & Aktiviti' : ($filterItems->firstWhere('id', $selectedFilter)['name'] ?? 'Item Terpilih')); ?>',
                                items: <?php echo e(json_encode($filterItems->map(function($i){ return ['id' => $i['id'], 'name' => $i['name'] ]; })->prepend(['id' => 'all', 'name' => 'Semua Pelajaran & Aktiviti']))); ?>,
                                get filteredItems() {
                                    if (this.search === '') return this.items;
                                    return this.items.filter(item => item.name.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                selectItem(id, name) {
                                    this.selectedIdentifier = id;
                                    this.selectedName = name;
                                    this.open = false;
                                    // Submit Form
                                    document.getElementById('hidden_filter_id').value = id;
                                    document.getElementById('filter_form').submit();
                                }
                             }">
                            <label class="text-sm font-medium text-gray-700">Pelajaran / Aktiviti</label>
                            <input type="hidden" name="filter_id" id="hidden_filter_id" value="<?php echo e($selectedFilter); ?>">
                            
                            <div class="relative min-w-[250px]">
                                <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                                    class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <span class="block truncate" x-text="selectedName"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>

                                <div x-show="open" @click.away="open = false" 
                                    class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                    style="display: none;">
                                    
                                    <div class="sticky top-0 z-10 bg-white p-2 border-b">
                                        <input x-ref="searchInput" x-model="search" type="text" placeholder="Cari..." 
                                            class="w-full border-gray-300 rounded-md text-sm p-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <ul class="py-1">
                                        <template x-for="item in filteredItems" :key="item.id">
                                            <li @click="selectItem(item.id, item.name)"
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 text-gray-900">
                                                <span class="block truncate" x-text="item.name" :class="{ 'font-semibold': item.id === selectedIdentifier, 'font-normal': item.id !== selectedIdentifier }"></span>
                                                <span x-show="item.id === selectedIdentifier" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </li>
                                        </template>
                                        <li x-show="filteredItems.length === 0" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500">
                                            Tiada hasil dijumpai.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <?php if($selectedClass || auth()->user()->role === 'student'): ?>

            <!-- Analisis Perkembangan / Prestasi Section (MOVED UP) -->
            <?php $stats = request('statistics', []); ?>
            <?php if(!empty($stats)): ?> 
                <div class="mt-6 mb-8">
                    <?php if(auth()->user()->role === 'teacher'): ?>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        Analisis Prestasi
                    </h3>
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        <!-- Card 1: Total Students (Teacher Only) -->
                        <?php if(auth()->user()->role === 'teacher'): ?>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-blue-500">
                            <div class="text-sm font-medium text-gray-500 uppercase">Jumlah Pelajar</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e($stats['total_students'] ?? '-'); ?></div>
                        </div>
                        <?php endif; ?>

                        <!-- Card 2: Completion / Lesson Count -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-green-500">
                            <?php if($mode === 'all'): ?>
                                <div class="text-sm font-medium text-gray-500 uppercase">Jumlah Pelajaran</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e($stats['total_items'] ?? '-'); ?></div>
                            <?php else: ?>
                                <div class="text-sm font-medium text-gray-500 uppercase"><?php echo e(auth()->user()->role === 'student' ? 'Status' : 'Pelajar Selesai'); ?></div>
                                <?php if(auth()->user()->role === 'student'): ?>
                                     <!-- For Student: Show "Completed" or "In Progress" -->
                                     <div class="mt-1 text-2xl font-semibold <?php echo e(($stats['completed_count'] ?? 0) > 0 ? 'text-green-600' : 'text-yellow-600'); ?>">
                                         <?php echo e(($stats['completed_count'] ?? 0) > 0 ? 'Selesai' : 'Belum Selesai'); ?>

                                     </div>
                                <?php else: ?>
                                    <div class="mt-1 flex items-baseline gap-2">
                                        <span class="text-2xl font-semibold text-gray-900"><?php echo e($stats['completed_count'] ?? '-'); ?></span>
                                        <span class="text-sm text-gray-500">of <?php echo e($stats['total_students'] ?? '-'); ?></span>
                                    </div>
                                    <?php if(isset($stats['not_completed_count'])): ?>
                                        <div class="text-xs text-red-500 mt-1">Belum Selesai: <?php echo e($stats['not_completed_count']); ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Card 3: 100% Achievers (Teacher Only) -->
                        <?php if(auth()->user()->role === 'teacher'): ?>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-yellow-500">
                            <div class="text-sm font-medium text-gray-500 uppercase">
                                <?php echo e($mode === 'all' ? 'Pelajar Cemerlang (100%)' : 'Skor Penuh (100%)'); ?>

                            </div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e($stats['hundred_percent_count'] ?? '-'); ?></div>
                            <div class="text-xs text-gray-500 mt-1">
                                <?php echo e($mode === 'all' ? 'Completed All to 100%' : 'Pelajar Cemerlang'); ?>

                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Card 4: Average Score / Completion -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-purple-500">
                            <div class="text-sm font-medium text-gray-500 uppercase">
                                <?php echo e($mode === 'all' ? 'Purata Markah' : (auth()->user()->role === 'student' ? 'Markah Anda' : 'Purata Kelas')); ?>

                            </div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">
                                <?php echo e(isset($stats['class_average']) ? $stats['class_average'].'%' : ($stats['average_score'] ?? '-').'%'); ?>

                            </div>
                        </div>

                    </div>
                </div>
            <?php endif; ?>

                <!-- Header Banner -->
                <div class="bg-blue-600 rounded-t-lg px-6 py-4">
                    <h3 class="text-lg font-medium text-white flex items-center gap-2">
                        <?php if($mode === 'all'): ?>
                            📊 Melihat: <?php echo e(auth()->user()->role === 'student' ? auth()->user()->full_name : ($selectedClass ? $selectedClass->name : 'Tiada Kelas')); ?> | Semua Pelajaran
                        <?php else: ?>
                            👉 Melihat: <?php echo e(auth()->user()->role === 'student' ? auth()->user()->full_name : ($selectedClass ? $selectedClass->name : 'Tiada Kelas')); ?> | <?php echo e($filterItems->firstWhere('id', $selectedFilter)['name'] ?? 'Item Terpilih'); ?>

                        <?php endif; ?>
                    </h3>
                </div>



                    <!-- Mode: Activity Detail -->
            <?php if(isset($mode) && $mode === 'activity_detail'): ?>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Analisis Prestasi: <?php echo e($activities->first()->title ?? 'Aktiviti'); ?></h3>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                        <table class="min-w-full divide-y divide-gray-200 relative">
                            <thead class="bg-gray-50 sticky top-0 z-20 shadow-sm">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-30 w-48 shadow-sm">
                                    Nama Pelajar
                                </th>
                                <?php 
                                    $questions = request('activity_questions', []); 
                                    $qCount = count($questions) > 0 ? count($questions) : request('question_count', 3);
                                ?>
                                
                                <?php if(count($questions) > 0): ?>
                                    <?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap min-w-[150px]" title="<?php echo e($q['question'] ?? 'Q'.($index+1)); ?>">
                                            <?php echo e(Str::limit($q['question'] ?? 'Q'.($index+1), 20)); ?>

                                        </th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <?php for($i=1; $i<=$qCount; $i++): ?>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            S<?php echo e($i); ?>

                                        </th>
                                    <?php endfor; ?>
                                <?php endif; ?>
                                
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider sticky right-0 bg-gray-50 z-30 w-32 shadow-sm">
                                    Gred
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white z-10 shadow-sm">
                                        <div class="flex items-center">
                                            <div class="ml-0">
                                                <div class="text-sm font-medium text-gray-900"><?php echo e($row['student']->full_name ?? $row['student']->username); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <?php $__currentLoopData = $row['answers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ans): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php if($ans === '✓'): ?> <span class="text-green-600 font-bold">✓</span>
                                            <?php elseif($ans === '✗'): ?> <span class="text-red-600 font-bold">✗</span>
                                            <?php elseif($ans === '?'): ?> <span class="text-yellow-600 font-bold" title="Score: <?php echo e($row['total_marks']); ?>%">?</span>
                                            <?php else: ?> <span class="text-gray-400">-</span> <?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900 sticky right-0 bg-white z-10 shadow-sm border-l">
                                        <?php echo e($row['total_marks'] !== '-' ? $row['total_marks'].'%' : '-'); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="100%" class="px-6 py-4 text-center text-sm text-gray-500">Tiada data pelajar.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- Mode: Lesson Detail -->
            <?php elseif(isset($mode) && $mode === 'lesson_detail'): ?>
                 <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Analisis Prestasi</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/2">
                                    Nama Pelajar
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/2">
                                    Gred
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-0">
                                                <div class="text-sm font-medium text-gray-900"><?php echo e($row['student']->full_name ?? $row['student']->username); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        <div x-data="{ editing: false, grade: '<?php echo e($row['grade'] !== '-' ? $row['grade'] : ''); ?>' }" class="flex items-center justify-center gap-2">
                                            <!-- Display Mode -->
                                            <div x-show="!editing" class="flex items-center gap-2">
                                                <span class="font-bold text-lg <?php echo e($row['grade'] == 100 ? 'text-green-600' : ''); ?>">
                                                    <?php echo e($row['grade']); ?>

                                                </span>
                                                <?php if(auth()->user()->role === 'teacher'): ?>
                                                    <button @click="editing = true; $nextTick(() => $refs.gradeInput.focus())" class="text-gray-400 hover:text-indigo-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Edit Mode -->
                                            <div x-show="editing" class="flex items-center" style="display: none;">
                                                <form action="<?php echo e(route('performance.update-grade')); ?>" method="POST" class="flex items-center gap-2">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="user_id" value="<?php echo e($row['user_id']); ?>">
                                                    <input type="hidden" name="lesson_id" value="<?php echo e($row['lesson_id']); ?>">
                                                    <input x-ref="gradeInput" type="number" name="grade" x-model="grade" min="0" max="100" class="w-16 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-1">
                                                    <button type="submit" class="text-green-600 hover:text-green-800">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" @click="editing = false" class="text-red-500 hover:text-red-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">Tiada data pelajar.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                 </div>

            <?php else: ?>
            <!-- Mode: All Lessons Summary (Existing) -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 w-48">
                                Nama Pelajar
                            </th>
                            <!-- Lesson Columns -->
                            <?php $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">
                                    <?php echo e($lesson->title); ?>

                                </th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <!-- Activity Columns -->
                            <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px] bg-indigo-50">
                                    <?php echo e($activity->title); ?>

                                </th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100 w-32">
                                Purata
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white z-10 shadow-sm border-r border-gray-100">
                                    <div class="flex items-center">
                                        <div class="ml-0">
                                            <div class="text-sm font-medium text-gray-900"><?php echo e($row['student']->full_name ?? $row['student']->username); ?></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Lesson Scores -->
                                <?php for($i=0; $i<$lessons->count(); $i++): ?> 
                                    <?php $lesson = $lessons[$i]; $grade = $row['grades'][$lesson->id] ?? null; ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        <?php if($grade): ?>
                                            <span class="font-bold <?php echo e($grade['display'] !== '-' ? 'text-gray-900' : 'text-gray-400'); ?>">
                                                <?php echo e($grade['display']); ?>

                                            </span>
                                        <?php else: ?> - <?php endif; ?>
                                    </td>
                                <?php endfor; ?>

                                <!-- Activity Scores -->
                                <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $actGrade = $row['activity_grades'][$activity->id] ?? null; ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 bg-indigo-50/30">
                                        <span class="font-bold <?php echo e($actGrade && $actGrade['display'] !== '-' ? 'text-indigo-700' : 'text-gray-400'); ?>">
                                            <?php echo e($actGrade ? $actGrade['display'] : '-'); ?>

                                        </span>
                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900 bg-gray-50 border-l border-gray-100">
                                    <?php echo e($row['average']); ?>%
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="100%" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tiada data.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>



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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Material\resources\views/performance/index.blade.php ENDPATH**/ ?>