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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="<?php echo e(route('performance.index')); ?>"
                        class="flex flex-wrap items-center gap-6">

                        <!-- Class Filter -->
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

                        <!-- Lesson Filter -->
                        <div class="flex flex-col gap-1">
                            <label for="lesson_id" class="text-sm font-medium text-gray-700">Pelajaran</label>
                            <select name="lesson_id" id="lesson_id" onchange="this.form.submit()"
                                class="block pl-3 pr-10 py-2 text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md min-w-[200px]">
                                <option value="all" class="text-gray-900 bg-white" <?php echo e($selectedLessonId === 'all' ? 'selected' : ''); ?>>Semua Pelajaran</option>
                                <?php $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($lesson->id); ?>" class="text-gray-900 bg-white" <?php echo e($selectedLessonId == $lesson->id ? 'selected' : ''); ?>>
                                        <?php echo e($lesson->title ?? 'Pelajaran ' . $loop->iteration); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                    </form>
                </div>
            </div>

            <?php if($selectedClass): ?>

                <!-- Header Banner -->
                <div class="bg-blue-600 rounded-t-lg px-6 py-4">
                    <h3 class="text-lg font-medium text-white flex items-center gap-2">
                        <?php if($mode === 'all'): ?>
                            📊 Melihat: <?php echo e($selectedClass->name); ?> | Semua Pelajaran
                        <?php else: ?>
                            📊 Melihat: <?php echo e($selectedClass->name); ?> |
                            <?php echo e($lessons->find($selectedLessonId)->title ?? 'Pelajaran Terpilih'); ?>

                        <?php endif; ?>
                    </h3>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-b-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            NAMA PELAJAR</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            KELAS</th>

                                        <?php if($mode === 'all'): ?>
                                            <?php $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                    <?php echo e(strtoupper($lesson->title ?? 'P ' . $loop->iteration)); ?>

                                                </th>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                PURATA KESELURUHAN</th>
                                        <?php else: ?>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                S1</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                S2</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                S3</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                GRED GURU</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                JUMLAH MARKAH (SISTEM)</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="text-sm font-medium text-gray-900 text-blue-600 hover:underline">
                                                        👤 <?php echo e($row['student']->full_name ?? $row['student']->name); ?>

                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo e($selectedClass->name); ?>

                                            </td>

                                            <?php if($mode === 'all'): ?>
                                                <?php $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <?php
                                                            $grade = $row['grades'][$lesson->id];
                                                        ?>
                                                        <?php if($grade['display'] !== '-'): ?>
                                                            <span
                                                                class="<?php echo e($grade['score'] == 3 ? 'text-green-600' : ($grade['score'] < 2 ? 'text-red-500' : 'text-gray-900')); ?>">
                                                                <?php echo e($grade['display']); ?>

                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-gray-400">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                                    <?php echo e($row['average']); ?>

                                                </td>
                                            <?php else: ?>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <?php if($row['s1'] === '✓'): ?> <span class="text-green-500 font-bold">✓</span>
                                                    <?php elseif($row['s1'] === '✗'): ?> <span class="text-red-500 font-bold">✗</span>
                                                    <?php else: ?> <span class="text-gray-300">-</span> <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <?php if($row['s2'] === '✓'): ?> <span class="text-green-500 font-bold">✓</span>
                                                    <?php elseif($row['s2'] === '✗'): ?> <span class="text-red-500 font-bold">✗</span>
                                                    <?php else: ?> <span class="text-gray-300">-</span> <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <?php if($row['s3'] === '✓'): ?> <span class="text-green-500 font-bold">✓</span>
                                                    <?php elseif($row['s3'] === '✗'): ?> <span class="text-red-500 font-bold">✗</span>
                                                    <?php else: ?> <span class="text-gray-300">-</span> <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-lg">
                                                    <?php if($row['teacher_grade'] !== '-'): ?>
                                                        <div>
                                                            <span class="text-indigo-600 block"><?php echo e($row['teacher_grade']); ?>%</span>
                                                            <span class="text-xs text-gray-400 font-normal">Gred Guru</span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-gray-300">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span
                                                        class="<?php echo e($row['total_marks'] == 3 ? 'text-green-600' : ($row['total_marks'] < 2 ? 'text-red-500' : 'text-blue-600')); ?> font-bold text-lg">
                                                        <?php echo e($row['total_marks']); ?>

                                                    </span>
                                                    <span class="text-xs text-gray-400 font-normal">/3</span>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="100%" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                                Tiada data dijumpai.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Material\resources\views/performance/index.blade.php ENDPATH**/ ?>