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
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-extrabold text-[#2454FF] mb-2">Interactive Quiz: Interaction Design</h1>
                <p class="text-gray-600">Current Total Points: <strong class="text-[#F26430]"><?php echo e($points); ?> XP</strong></p>
            </div>

            <?php if(session('quiz_result')): ?>
                <?php $result = session('quiz_result'); ?>
                <div class="quiz-feedback p-6 border-2 border-[#5FAD56] bg-green-50 rounded-lg mb-6 text-center">
                    <h3 class="text-2xl font-bold <?php echo e($result['percentage'] >= 50 ? 'text-[#5FAD56]' : 'text-[#E92222]'); ?> mb-3">
                        Quiz Complete!
                    </h3>
                    <p class="text-lg mb-2">You answered <strong><?php echo e($result['score']); ?> out of <?php echo e($result['total']); ?></strong> questions correctly (<?php echo e(round($result['percentage'])); ?>%).</p>
                    <p class="text-xl font-bold text-[#F26430] mb-2">🎉 Points Earned: +<?php echo e($result['points_awarded']); ?> XP</p>
                    <p class="text-gray-700">Your Total Score: <strong><?php echo e($result['total_points']); ?> Points</strong></p>
                </div>
            <?php endif; ?>

            <?php if($hasSubmitted && !session('quiz_result')): ?>
                <div class="quiz-feedback p-6 border-2 border-[#5FAD56] bg-green-50 rounded-lg mb-6 text-center">
                    <h3 class="text-xl font-bold text-[#5FAD56] mb-2">You have already completed this quiz!</h3>
                    <p class="text-gray-700">Score: <?php echo e($lastAttempt->score); ?>/<?php echo e($lastAttempt->total_questions); ?></p>
                    <p class="text-gray-700">Points Earned: <?php echo e($lastAttempt->points_earned); ?> XP</p>
                </div>
            <?php elseif(!$hasSubmitted || session('quiz_result')): ?>
                <form method="POST" action="<?php echo e(route('quiz.submit')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="lesson_id" value="<?php echo e($lesson); ?>">
                    
                    <?php $__currentLoopData = $quizData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="question border border-gray-300 rounded-lg p-4 mb-4">
                            <h4 class="text-lg font-semibold text-[#2454FF] mb-3">Question <?php echo e($index + 1); ?>:</h4>
                            <p class="text-gray-700 mb-3"><?php echo e($q['question']); ?></p>
                            
                            <div class="space-y-2">
                                <?php $__currentLoopData = $q['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="radio" name="q<?php echo e($index); ?>" value="<?php echo e($option); ?>" required class="mr-3">
                                        <span><?php echo e($option); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <button type="submit" name="submit_quiz" 
                            class="w-full bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150">
                        Submit Quiz (Reinforce Learning)
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-6 text-center">
                <a href="<?php echo e(route('lesson.index')); ?>" class="text-[#2454FF] hover:underline">Back to Lessons</a>
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

<?php /**PATH C:\xampp\htdocs\Material\resources\views/quiz/show.blade.php ENDPATH**/ ?>