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
                <h2 class="text-2xl font-bold text-gray-900">Jejak Perkembangan Pelajaran</h2>
                <p class="text-gray-600">Pantau status penyelesaian pelajar merentasi semua pelajaran</p>
            </div>

            <?php
                // Build Filter Items for JS (Moved to top)
                $filterItems = collect();
                if($selectedClass) {
                    foreach($lessons as $l) $filterItems->push(['id' => 'lesson-'.$l->id, 'name' => $l->title]);
                    foreach($activities as $a) $filterItems->push(['id' => 'activity-'.$a->id, 'name' => $a->title]);
                }
                $filterItems = $filterItems->values();
            ?>

            <!-- Filters & Search Wrapper -->
            <div class="bg-white shadow-sm sm:rounded-lg mb-6"
                 x-data="{ 
                    open: false, 
                    search: '', 
                    selectedFilter: 'all',
                    selectedName: 'Semua Pelajaran & Aktiviti',
                    items: <?php echo e(json_encode($filterItems->prepend(['id' => 'all', 'name' => 'Semua Pelajaran & Aktiviti']))); ?>,
                    get filteredItems() {
                        if (this.search === '') return this.items;
                        return this.items.filter(item => item.name.toLowerCase().includes(this.search.toLowerCase()));
                    },
                    selectItem(id, name) {
                        this.selectedFilter = id;
                        this.selectedName = name;
                        this.open = false;
                    }
                 }">
                <div class="p-6 bg-white border-b border-gray-200 flex flex-col md:flex-row gap-6 items-start md:items-end">
                    
                    <!-- Class Selector (Server) -->
                    <form method="GET" action="<?php echo e(route('progress.index')); ?>" class="w-full md:w-auto">
                        <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <select name="class_id" id="class_id" onchange="this.form.submit()"
                            class="block w-full min-w-[200px] pl-3 pr-10 py-2 text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                            <option value="" disabled <?php echo e(!$selectedClass ? 'selected' : ''); ?>>Sila Pilih Kelas</option>
                            <?php $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($classroom->id); ?>" <?php echo e(($selectedClass && $selectedClass->id == $classroom->id) ? 'selected' : ''); ?>>
                                    <?php echo e($classroom->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </form>

                    <!-- Activity/Lesson Search (Client Alpine) -->
                    <?php if($selectedClass): ?>
                    <div class="relative w-full md:w-auto min-w-[300px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tapis Pelajaran / Aktiviti</label>
                        <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                            class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span class="block truncate text-gray-900" x-text="selectedName"></span>
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
                                        <span class="block truncate" x-text="item.name" :class="{ 'font-bold': item.id === selectedFilter }"></span>
                                        <span x-show="item.id === selectedFilter" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600">
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
                    <?php endif; ?>
                </div>
            </div>

            <?php if($selectedClass): ?>
                
                <!-- Analisis Perkembangan Section (MOVED UP) -->
                <div class="bg-white shadow-sm sm:rounded-lg mb-6 p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Analisis Perkembangan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        <!-- Card 1: Total Students -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-blue-500">
                            <div class="text-sm font-medium text-gray-500 uppercase">Jumlah Pelajar</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e($summary['totalStudents']); ?></div>
                        </div>

                        <!-- Card 2: Total Lessons & Activities -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-green-500">
                            <div class="text-sm font-medium text-gray-500 uppercase">Jumlah Pelajaran</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e($summary['totalLessons']); ?></div>
                        </div>

                        <!-- Card 3: 100% Completers -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-yellow-500">
                            <div class="text-sm font-medium text-gray-500 uppercase">Pelajar Selesai (100%)</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e($summary['hundredPercentCount'] ?? 0); ?></div>
                            <div class="text-xs text-gray-500 mt-1">Status Cemerlang</div>
                        </div>

                        <!-- Card 4: Class Average -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-purple-500">
                            <div class="text-sm font-medium text-gray-500 uppercase">Purata Penyiapan</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e($summary['classAverage'] ?? 0); ?>%</div>
                        </div>

                    </div>
                </div>

                <!-- Progress Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    
                    <!-- Header with Title Only -->
                    <div class="bg-blue-600 px-6 py-4 border-b border-blue-600 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-white flex items-center gap-2">
                            📊 Jadual Perkembangan Pelajaran - <?php echo e($selectedClass->name); ?>

                        </h3>
                    </div>

                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            NAMA PELAJAR</th>
                                        <?php $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <th scope="col" x-show="selectedFilter === 'all' || selectedFilter === 'lesson-<?php echo e($lesson->id); ?>'"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                <?php echo e(strtoupper($lesson->title ?? 'PELAJARAN ' . $loop->iteration)); ?>

                                            </th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        
                                        <!-- Activities Header -->
                                        <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <th scope="col" x-show="selectedFilter === 'all' || selectedFilter === 'activity-<?php echo e($activity->id); ?>'"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-l border-gray-200">
                                                <?php echo e(strtoupper($activity->title ?? 'AKTIVITI ' . $loop->iteration)); ?>

                                            </th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            PERATUS PENYELESAIAN</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $__empty_1 = true; $__currentLoopData = $progressData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $progress): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="text-sm font-medium text-gray-900 text-blue-600 hover:underline">
                                                        👤 <?php echo e($progress['student']->full_name ?? $progress['student']->name); ?>

                                                    </div>
                                                </div>
                                            </td>
                                            <?php $__currentLoopData = $progress['lessons']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lessonProgress): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <td class="px-6 py-4 whitespace-nowrap text-center"
                                                    x-show="selectedFilter === 'all' || selectedFilter === 'lesson-<?php echo e($lessonProgress['lesson']->id); ?>'">
                                                    <?php
                                                        $statusClass = 'bg-gray-100 text-gray-800';
                                                        $statusText = 'BELUM MULA';

                                                        if ($lessonProgress['status'] === 'Completed') {
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                            $statusText = 'SELESAI';
                                                        } elseif ($lessonProgress['status'] === 'In Progress') {
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                            $statusText = 'SEDANG BERJALAN (' . $lessonProgress['progress'] . '%)';
                                                        }
                                                    ?>
                                                    <span
                                                        class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full <?php echo e($statusClass); ?>">
                                                        <?php echo e($statusText); ?>

                                                    </span>
                                                </td>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            
                                            <!-- Activity Statuses -->
                                            <?php $__currentLoopData = $progress['activities']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activityProgress): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <td class="px-6 py-4 whitespace-nowrap text-center border-l border-gray-200"
                                                    x-show="selectedFilter === 'all' || selectedFilter === 'activity-<?php echo e($activityProgress['activity']->id); ?>'">
                                                    <?php
                                                        $statusClass = 'bg-gray-100 text-gray-800';
                                                        $statusText = 'BELUM MULA';

                                                        if ($activityProgress['status'] === 'Completed') {
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                            $statusText = 'SELESAI (' . $activityProgress['score'] . ')';
                                                        }
                                                    ?>
                                                    <span
                                                        class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full <?php echo e($statusClass); ?>">
                                                        <?php echo e($statusText); ?>

                                                    </span>
                                                </td>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-24 bg-gray-200 rounded-full h-2.5">
                                                        <div class="bg-blue-600 h-2.5 rounded-full"
                                                            style="width: <?php echo e($progress['completionPercentage']); ?>%"></div>
                                                    </div>
                                                    <span
                                                        class="text-sm font-bold text-gray-700"><?php echo e($progress['completionPercentage']); ?>%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="<?php echo e(count($lessons) + count($activities) + 2); ?>"
                                                class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                                Tiada pelajar dijumpai dalam kelas ini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            <?php else: ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    Sila pilih kelas untuk melihat perkembangan.
                </div>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Material\resources\views/progress/index.blade.php ENDPATH**/ ?>