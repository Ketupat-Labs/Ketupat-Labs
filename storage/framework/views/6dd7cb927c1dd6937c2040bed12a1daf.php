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
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ 
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
        <div class="mb-6 px-4 sm:px-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-[#2454FF] tracking-tight">Pelajaran Tersedia</h1>
                <p class="text-gray-600 mt-2">Layari dan akses semua pelajaran yang diterbitkan</p>
            </div>
            <!-- Search Bar -->
            <div class="w-full sm:w-72">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Cari pelajaran..." 
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition shadow-sm text-gray-700">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-l-4 border-[#2454FF] pl-3">Pelajaran</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php
                            $lessons = $items->where('item_type', 'lesson');
                        ?>
                        
                        <?php $__empty_1 = true; $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border <?php echo e($item->is_completed ? 'border-gray-200 bg-gray-50' : 'border-gray-200 bg-white'); ?> rounded-lg p-4 hover:shadow-lg transition-shadow relative"
                                 x-show="fuzzyMatch('<?php echo e($item->title); ?>', search) || fuzzyMatch('<?php echo e($item->topic); ?>', search)"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100">
                                <h3 class="text-xl font-semibold text-[#2454FF] mb-2 <?php echo e($item->is_completed ? 'text-gray-500 line-through' : ''); ?>"><?php echo e($item->title); ?></h3>
                                
                                <?php if($item->is_completed): ?>
                                    <span class="absolute top-4 right-4 bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded uppercase flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Selesai
                                    </span>
                                <?php endif; ?>
                                
                                <p class="text-gray-600 text-sm mb-2">Topik: <?php echo e($item->topic); ?></p>
                                <?php if($item->duration): ?>
                                <p class="text-gray-500 text-sm mb-4">Durasi: <?php echo e($item->duration); ?> minit</p>
                                <?php endif; ?>
                                <a href="<?php echo e(route('lesson.show', $item->id)); ?>" 
                                   class="inline-flex items-center <?php echo e($item->is_completed ? 'bg-gray-400 hover:bg-gray-500' : 'bg-[#5FAD56] hover:bg-green-700'); ?> text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150 shadow-sm hover:shadow-md">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    <?php echo e($item->is_completed ? 'Lihat Semula' : 'Lihat Pelajaran'); ?>

                                </a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-span-full text-center py-6 bg-gray-50 rounded-lg">
                                <p class="text-gray-500">Tiada pelajaran tersedia buat masa ini.</p>
                            </div>
                        <?php endif; ?>
                        <div x-show="search !== '' && $el.parentNode.querySelectorAll('div[x-show]:not([style*=\'display: none\'])').length === 0" 
                             class="col-span-full text-center py-6 bg-gray-50 rounded-lg" style="display: none;">
                            <p class="text-gray-500 italic">Tiada pelajaran dijumpai.</p>
                        </div>
                    </div>
                </div>

                
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-l-4 border-purple-600 pl-3">Aktiviti</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php
                            $activities = $items->where('item_type', 'activity');
                        ?>

                        <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border <?php echo e($item->is_completed ? 'border-gray-200 bg-gray-50' : 'border-purple-200 bg-purple-50'); ?> rounded-lg p-4 hover:shadow-lg transition-shadow relative"
                                 x-show="fuzzyMatch('<?php echo e($item->title); ?>', search) || fuzzyMatch('<?php echo e($item->type); ?>', search)"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-xl font-semibold <?php echo e($item->is_completed ? 'text-gray-500 line-through' : 'text-purple-700'); ?>"><?php echo e($item->title); ?></h3>
                                    
                                    <?php if($item->is_completed): ?>
                                        <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded uppercase flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-purple-200 text-purple-800 text-xs font-bold px-2 py-1 rounded uppercase">Aktiviti</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-600 text-sm mb-2 font-medium"><?php echo e($item->type); ?></p>
                                <p class="text-gray-500 text-sm mb-4">Duration: <?php echo e($item->suggested_duration); ?></p>
                                <?php if($item->due_date): ?>
                                     <p class="<?php echo e($item->is_completed ? 'text-gray-400' : 'text-red-500 font-bold'); ?> text-sm mb-4">
                                        Due: <?php echo e(\Carbon\Carbon::parse($item->due_date)->format('M d, Y')); ?>

                                     </p>
                                <?php endif; ?>
                                <a href="<?php echo e(route('activities.show', $item->id)); ?>" 
                                   class="inline-flex items-center <?php echo e($item->is_completed ? 'bg-gray-400 hover:bg-gray-500' : 'bg-purple-600 hover:bg-purple-700'); ?> text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150 shadow-sm hover:shadow-md">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <?php echo e($item->is_completed ? 'Lihat Semula' : 'Mula Aktiviti'); ?>

                                </a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-span-full text-center py-6 bg-gray-50 rounded-lg">
                                <p class="text-gray-500">No activities assigned yet.</p>
                            </div>
                        <?php endif; ?>
                         <div x-show="search !== '' && $el.parentNode.querySelectorAll('div[x-show]:not([style*=\'display: none\'])').length === 0" 
                             class="col-span-full text-center py-6 bg-gray-50 rounded-lg" style="display: none;">
                            <p class="text-gray-500 italic">Tiada aktiviti dijumpai.</p>
                        </div>
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

<?php /**PATH C:\xampp\htdocs\Material\resources\views/lessons/student-index.blade.php ENDPATH**/ ?>