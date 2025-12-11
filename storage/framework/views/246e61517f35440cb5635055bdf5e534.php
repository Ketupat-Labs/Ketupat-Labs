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
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 card">
            <div class="flex justify-between items-center mb-6 border-b-2 border-[#2454FF] pb-2">
                <h2 class="text-2xl font-bold text-gray-800">
                    Create New Lesson
                </h2>
                <a href="<?php echo e(route('lessons.index')); ?>" class="text-gray-600 hover:text-gray-900 font-medium">
                    ← Back to Lessons
                </a>
            </div>

            <?php if($errors->any()): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <strong class="font-bold">Validation Error!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <li><?php echo e($error); ?></li> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('lessons.store')); ?>" enctype="multipart/form-data" class="space-y-6">
                <?php echo csrf_field(); ?>

                <div>
                    <label for="title" class="block font-medium text-lg text-[#3E3E3E]">Lesson Title <span class="text-red-600">*</span></label>
                    <input type="text" name="title" id="title" required
                           class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                           value="<?php echo e(old('title')); ?>">
                </div>

                <div>
                    <label for="topic" class="block font-medium text-lg text-[#3E3E3E]">Module / Topic <span class="text-red-600">*</span></label>
                    <input type="text" name="topic" id="topic" list="topics" required
                           class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                           value="<?php echo e(old('topic')); ?>" placeholder="Select or type a topic">
                    <datalist id="topics">
                        <option value="HCI">3.1 Interaction Design</option>
                        <option value="HCI_SCREEN">3.2 Screen Design</option>
                        <option value="Algorithm">Other: Algorithms</option>
                    </datalist>
                </div>

                <div>
                    <label for="content" class="block font-medium text-lg text-[#3E3E3E]">Lesson Content (Context) <span class="text-red-600">*</span></label>
                    <textarea name="content" id="content" rows="6" required
                              class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                              placeholder="Write the lesson content here..."><?php echo e(old('content')); ?></textarea>
                </div>
                
                <div>
                    <label for="duration" class="block font-medium text-lg text-[#3E3E3E]">Estimated Duration (Mins)</label>
                    <input type="number" name="duration" id="duration" min="5"
                           class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                           value="<?php echo e(old('duration')); ?>">
                </div>

                <div>
                    <label for="url" class="block font-medium text-lg text-[#3E3E3E]">Lesson URL (Optional)</label>
                    <input type="url" name="url" id="url"
                           class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"
                           value="<?php echo e(old('url')); ?>" placeholder="https://example.com">
                </div>

                <div>
                    <label for="material_file" class="block font-medium text-lg text-[#3E3E3E]">Lesson Material (PDF/File)</label>
                    <input type="file" name="material_file" id="material_file" accept=".pdf, .doc, .docx"
                           class="mt-1 block w-full p-3 border border-gray-400 rounded-md bg-gray-50 cursor-pointer">
                </div>

                <div class="flex items-center justify-start space-x-4 pt-4">
                    <button type="submit" class="bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150">
                        Save Lesson
                    </button>
                    <a href="<?php echo e(route('lessons.index')); ?>" class="text-gray-600 hover:text-gray-900 font-medium">
                        Cancel
                    </a>
                </div>
            </form>
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
<?php endif; ?><?php /**PATH /Users/raudhahmaszamanie/Downloads/Ketupat-Labs-fix-REALMAIN/resources/views/lessons/create.blade.php ENDPATH**/ ?>