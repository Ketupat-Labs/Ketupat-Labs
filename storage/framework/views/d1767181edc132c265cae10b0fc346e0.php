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
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Grade Submission')); ?>

            </h2>
            <a href="<?php echo e(route('submission.index')); ?>" class="text-gray-500 hover:text-gray-700 text-sm">
                &larr; Back to Submissions
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Left Column: Submission Details & File -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Submission Details</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700">Student</label>
                        <p class="text-gray-900"><?php echo e($submission->user->full_name ?? 'Unknown'); ?></p>
                        <p class="text-sm text-gray-500"><?php echo e($submission->user->email ?? ''); ?></p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700">Assignment</label>
                        <p class="text-gray-900"><?php echo e($submission->assignment_name); ?></p>
                        <p class="text-sm text-gray-500">Submitted: <?php echo e($submission->created_at->format('M d, Y H:i')); ?>

                        </p>
                    </div>

                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200 text-center">
                        <p class="text-sm text-gray-600 mb-2">Student File:</p>
                        <?php if($submission->file_path): ?>
                            <div class="mb-4">
                                <span
                                    class="font-mono text-xs bg-gray-200 px-2 py-1 rounded"><?php echo e($submission->file_name); ?></span>
                            </div>
                            <a href="<?php echo e(route('submission.file', $submission->id)); ?>" target="_blank"
                                class="inline-block bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                                View / Download File
                            </a>
                        <?php else: ?>
                            <p class="text-red-500 font-bold">No file uploaded.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: Grading Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Grading</h3>

                    <?php if(session('success')): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline"><?php echo e(session('success')); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <ul class="list-disc list-inside">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('submission.grade', $submission->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>

                        <div class="mb-6">
                            <label for="grade" class="block text-gray-700 text-sm font-bold mb-2">Grade (0-100)</label>
                            <input type="number" name="grade" id="grade" placeholder="Enter grade..." min="0" max="100"
                                required value="<?php echo e(old('grade', $submission->grade)); ?>"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg font-bold text-center w-32">
                        </div>

                        <div class="mb-6">
                            <label for="feedback" class="block text-gray-700 text-sm font-bold mb-2">Feedback
                                (Optional)</label>
                            <textarea name="feedback" id="feedback" rows="6"
                                placeholder="Write feedback for the student here..."
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo e(old('feedback', $submission->feedback)); ?></textarea>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit"
                                class="bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-8 rounded focus:outline-none focus:shadow-outline transform transition hover:scale-105 duration-150">
                                Submit Grade
                            </button>
                        </div>
                    </form>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Material\resources\views/submission/grade.blade.php ENDPATH**/ ?>