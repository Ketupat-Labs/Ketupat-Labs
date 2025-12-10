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
            <?php echo e(__('Profile')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Profile Header -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-8">
                    <div class="flex items-start space-x-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                <?php if($profileUser->avatar_url): ?>
                                    <img src="<?php echo e(asset($profileUser->avatar_url)); ?>" alt="<?php echo e($profileUser->full_name); ?>" class="h-full w-full object-cover">
                                <?php else: ?>
                                    <span class="text-4xl font-bold text-gray-600">
                                        <?php echo e(strtoupper(substr($profileUser->full_name ?? $profileUser->username ?? 'U', 0, 1))); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Profile Info -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e($profileUser->full_name ?? $profileUser->username); ?></h1>
                                    <?php if($profileUser->role): ?>
                                        <span class="inline-block mt-2 px-3 py-1 text-xs font-semibold rounded-full <?php echo e($profileUser->role === 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                                            <?php echo e($profileUser->role === 'teacher' ? 'Teacher' : 'Student'); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex space-x-3">
                                    <?php if($isOwnProfile): ?>
                                        <a href="<?php echo e(route('profile.edit')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            <i class="fas fa-edit mr-2"></i> Edit Profile
                                        </a>
                                    <?php else: ?>
                                        <?php if($isFriend): ?>
                                            <button onclick="removeFriend(<?php echo e($profileUser->id); ?>)" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                                                <i class="fas fa-user-minus mr-2"></i> Remove Friend
                                            </button>
                                        <?php elseif($hasPendingRequest): ?>
                                            <button class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg cursor-not-allowed" disabled>
                                                <i class="fas fa-clock mr-2"></i> Request Pending
                                            </button>
                                        <?php else: ?>
                                            <button onclick="addFriend(<?php echo e($profileUser->id); ?>)" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                <i class="fas fa-user-plus mr-2"></i> Add Friend
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Profile Details -->
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php if($profileUser->school): ?>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">School</span>
                                        <p class="text-gray-900"><?php echo e($profileUser->school); ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if($profileUser->class): ?>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Class</span>
                                        <p class="text-gray-900"><?php echo e($profileUser->class); ?></p>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Friends</span>
                                    <p class="text-gray-900"><?php echo e($friendCount); ?></p>
                                </div>
                            </div>

                            <?php if($profileUser->bio): ?>
                                <div class="mt-6">
                                    <span class="text-sm font-medium text-gray-500">Bio</span>
                                    <p class="text-gray-900 mt-2"><?php echo e($profileUser->bio); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Tabs -->
            <div class="bg-white shadow rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        <?php if($isOwnProfile): ?>
                            <button onclick="showSection('saved')" id="tab-saved" class="section-tab active py-4 px-6 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                                <i class="fas fa-bookmark mr-2"></i>Saved Posts
                            </button>
                        <?php endif; ?>
                        <button onclick="showSection('posts')" id="tab-posts" class="section-tab <?php echo e($isOwnProfile ? '' : 'active'); ?> py-4 px-6 text-sm font-medium border-b-2 <?php echo e($isOwnProfile ? 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' : 'border-blue-500 text-blue-600'); ?>">
                            <i class="fas fa-comments mr-2"></i>Forum Posts
                        </button>
                        <button onclick="showSection('badges')" id="tab-badges" class="section-tab py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-trophy mr-2"></i>Badges
                        </button>
                    </nav>
                </div>

                <!-- Saved Posts Section -->
                <?php if($isOwnProfile): ?>
                    <div id="section-saved" class="section-content p-6">
                        <?php if($savedPosts->count() > 0): ?>
                            <div class="space-y-4">
                                <?php $__currentLoopData = $savedPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <a href="<?php echo e(route('forum.post.detail', $post->id)); ?>" class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                                    <?php echo e($post->title); ?>

                                                </a>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    in <span class="font-medium"><?php echo e($post->forum->title); ?></span>
                                                    <span class="mx-2">•</span>
                                                    <?php echo e($post->created_at->diffForHumans()); ?>

                                                </p>
                                                <?php if($post->content): ?>
                                                    <p class="text-gray-700 mt-2 line-clamp-2"><?php echo e(Str::limit(strip_tags($post->content), 150)); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-8">No saved posts yet</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Forum Posts Section -->
                <div id="section-posts" class="section-content <?php echo e($isOwnProfile ? 'hidden' : ''); ?> p-6">
                    <?php if($posts->count() > 0): ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <a href="<?php echo e(route('forum.post.detail', $post->id)); ?>" class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                                <?php echo e($post->title); ?>

                                            </a>
                                            <p class="text-sm text-gray-600 mt-1">
                                                in <span class="font-medium"><?php echo e($post->forum->title); ?></span>
                                                <span class="mx-2">•</span>
                                                <?php echo e($post->created_at->diffForHumans()); ?>

                                            </p>
                                            <?php if($post->content): ?>
                                                <p class="text-gray-700 mt-2 line-clamp-2"><?php echo e(Str::limit(strip_tags($post->content), 150)); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No posts yet</p>
                    <?php endif; ?>
                </div>

                <!-- Badges Section -->
                <div id="section-badges" class="section-content hidden p-6">
                    <?php if($badges->count() > 0): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php $__currentLoopData = $badges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                                    <?php if($badge->icon_url): ?>
                                        <img src="<?php echo e(asset($badge->icon_url)); ?>" alt="<?php echo e($badge->badge_name); ?>" class="w-10 h-10">
                                    <?php else: ?>
                                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-trophy text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900"><?php echo e($badge->badge_name); ?></p>
                                        <?php if($badge->description): ?>
                                            <p class="text-sm text-gray-600"><?php echo e($badge->description); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No badges yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.section-tab').forEach(tab => {
                tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected section
            const section = document.getElementById('section-' + sectionName);
            if (section) {
                section.classList.remove('hidden');
            }
            
            // Add active class to selected tab
            const tab = document.getElementById('tab-' + sectionName);
            if (tab) {
                tab.classList.add('active', 'border-blue-500', 'text-blue-600');
                tab.classList.remove('border-transparent', 'text-gray-500');
            }
        }

        async function addFriend(friendId) {
            try {
                const response = await fetch('/api/friends/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ friend_id: friendId })
                });

                const data = await response.json();
                
                if (data.status === 200) {
                    alert('Friend request sent!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to send friend request');
                }
            } catch (error) {
                console.error('Error adding friend:', error);
                alert('Failed to send friend request');
            }
        }

        async function removeFriend(friendId) {
            if (!confirm('Are you sure you want to remove this friend?')) {
                return;
            }

            try {
                const response = await fetch('/api/friends/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ friend_id: friendId })
                });

                const data = await response.json();
                
                if (data.status === 200) {
                    alert('Friend removed');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to remove friend');
                }
            } catch (error) {
                console.error('Error removing friend:', error);
                alert('Failed to remove friend');
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
<?php endif; ?>

<?php /**PATH C:\xampp\htdocs\Material\resources\views/profile/show.blade.php ENDPATH**/ ?>