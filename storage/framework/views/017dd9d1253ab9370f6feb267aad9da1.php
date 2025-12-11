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
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-start pb-4 border-b border-gray-200 mb-4">
                    <h2 class="text-3xl font-extrabold text-[#2454FF]">
                        <?php echo e($lesson->title); ?> <span class="text-base text-gray-500">(<?php echo e($lesson->topic); ?>)</span>
                    </h2>
                    <a href="<?php echo e(route('lesson.index')); ?>" class="text-[#5FAD56] hover:text-green-700 font-medium">
                        &larr; Back to Lessons
                    </a>
                </div>

                
                <div class="progress-bar-area bg-gray-200 h-6 rounded-full mb-6">
                    <div class="progress-fill h-full text-center text-white bg-[#5FAD56] rounded-full"
                        style="width: 50%;">
                        50% Complete
                    </div>
                </div>

                <div class="lesson-content-card space-y-6">

                    <div class="clearfix">
                        <button
                            class="bg-[#F26430] hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg chat-button float-right"
                            onclick="alert('Launching Chatbot Interface (M4)...')">
                            Ask for Help (M4)
                        </button>
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">1. Lesson Content</h3>
                        <div class="mt-3 text-gray-700 prose max-w-none">
                            <?php echo nl2br(e($lesson->content)); ?>

                        </div>
                    </div>

                    <div class="pt-4">
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">2. Embedded Video Demonstration
                        </h3>
                        <div class="text-center my-6">
                            <a href="https://youtu.be/AawJ9IIdJtk?si=kle7vJJBaZKhvYbB" target="_blank"
                                class="inline-block">
                                <img src="https://placehold.co/500x300/F26430/ffffff?text=Click+to+Watch+Video"
                                    alt="Video Placeholder: Click to watch externally"
                                    class="border-4 border-[#F26430] cursor-pointer rounded-lg hover:opacity-90 transition-opacity">
                            </a>
                            <p class="text-sm text-gray-600 mt-2">Click image to view on YouTube</p>
                        </div>
                    </div>

                    <div class="pt-4">
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">3. Visual Guide: Empathy Stage
                            Flow</h3>
                        <div class="visual-guide border-2 border-[#F26430] p-4 rounded-lg bg-red-50 mt-4">
                            <img src="https://placehold.co/600x400/F26430/ffffff?text=Empathy+Flowchart"
                                alt="Flowchart of the Empathy Stage"
                                class="w-full h-auto border border-gray-400 rounded mb-3">
                            <p class="text-gray-700">This visual guide breaks down the abstract concept of the
                                <strong>Empathise stage</strong>, showing how designers gather user data before defining
                                the problem.
                            </p>
                        </div>
                    </div>

                    <div class="pt-4">
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">4. Lesson Materials</h3>
                        <?php if($lesson->material_path): ?>
                            <p class="mt-2 text-lg">Downloadable Material:
                                <a href="<?php echo e(Storage::url($lesson->material_path)); ?>" target="_blank"
                                    class="text-[#5FAD56] hover:underline font-bold">
                                    <?php echo e(basename($lesson->material_path)); ?>

                                </a>
                            </p>
                        <?php else: ?>
                            <p class="mt-2 text-gray-500">No physical material file available for this lesson.</p>
                        <?php endif; ?>
                    </div>

                    <div class="pt-4">
                        <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">5. Practical Exercise Submission
                        </h3>
                        <p class="mb-4 text-gray-700 mt-2">Upload your practical exercise file here for grading.</p>

                        <?php if(session('success')): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <span class="block sm:inline"><?php echo e(session('success')); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if(session('error')): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <span class="block sm:inline"><?php echo e(session('error')); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if($errors->any()): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <strong class="font-bold">Whoops!</strong>
                                <span class="block sm:inline">There were some problems with your input.</span>
                                <ul class="mt-2 list-disc list-inside text-sm">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($submission)): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <h4 class="font-bold text-lg mb-2">Submission Status</h4>
                                <p><strong>Status:</strong>
                                    <span
                                        class="<?php if($submission->status == 'Graded'): ?> text-green-600 <?php else: ?> text-orange-600 <?php endif; ?> font-bold">
                                        <?php echo e($submission->status); ?>

                                    </span>
                                </p>
                                <p><strong>File:</strong> <?php echo e($submission->file_name); ?></p>
                                <p><strong>Submitted:</strong> <?php echo e($submission->created_at->format('d M Y, H:i')); ?></p>

                                <?php if($submission->status == 'Graded'): ?>
                                    <div class="mt-3 pt-3 border-t border-blue-200">
                                        <p class="text-lg"><strong>Grade:</strong> <?php echo e($submission->grade); ?>/100</p>
                                        <?php if($submission->feedback): ?>
                                            <p class="mt-1"><strong>Feedback:</strong> <?php echo e($submission->feedback); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if($submission->status !== 'Graded'): ?>
                                <p class="text-sm text-gray-500 mb-2">You can re-upload to update your submission before
                                    grading.</p>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if(!isset($submission) || $submission->status !== 'Graded'): ?>
                            <form action="<?php echo e(route('submission.submit')); ?>" method="POST" enctype="multipart/form-data"
                                class="mt-4">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="lesson_id" value="<?php echo e($lesson->id); ?>">

                                <div class="mb-4">
                                    <label for="submission_file" class="block text-gray-700 text-sm font-bold mb-2">Upload
                                        File (HTML, ZIP, PNG, JPG, PDF, DOC, DOCX, TXT):</label>
                                    <input type="file" name="submission_file" id="submission_file" required
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <?php $__errorArgs = ['submission_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-red-500 text-xs italic"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <button type="submit"
                                    class="bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    <?php echo e(isset($submission) ? 'Update Submission' : 'Submit Assignment'); ?>

                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="pt-4">
                        <p class="mb-4 text-gray-700">Once you reach 100% completion, you can proceed to the assessment.
                        </p>
                        <a href="<?php echo e(route('quiz.show', $lesson->id)); ?>"
                            class="inline-block bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150">
                            Go to Gamified Quiz (UC007)
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Material\resources\views/lessons/student-show.blade.php ENDPATH**/ ?>