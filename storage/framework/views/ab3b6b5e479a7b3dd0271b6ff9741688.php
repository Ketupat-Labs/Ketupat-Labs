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
            <?php echo e(__('My Friends')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        <button onclick="showTab('friends')" id="tab-friends" class="tab-button active py-4 px-6 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                            <i class="fas fa-user-friends mr-2"></i>Friends (<?php echo e($friends->count()); ?>)
                        </button>
                        <button onclick="showTab('pending')" id="tab-pending" class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-clock mr-2"></i>Pending Requests (<?php echo e($pendingRequests->count()); ?>)
                        </button>
                        <button onclick="showTab('sent')" id="tab-sent" class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-paper-plane mr-2"></i>Sent Requests (<?php echo e($sentRequests->count()); ?>)
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Friends Tab -->
                    <div id="content-friends" class="tab-content">
                        <?php if($friends->count() > 0): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php $__currentLoopData = $friends; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $friend): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <?php if($friend->avatar_url): ?>
                                                    <img src="<?php echo e(asset($friend->avatar_url)); ?>" alt="<?php echo e($friend->full_name); ?>" class="h-12 w-12 rounded-full object-cover">
                                                <?php else: ?>
                                                    <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                        <?php echo e(strtoupper(substr($friend->full_name ?? $friend->username ?? 'U', 0, 1))); ?>

                                                    </div>
                                                <?php endif; ?>
                                                <?php if($friend->is_online): ?>
                                                    <span class="absolute -bottom-1 -right-1 h-4 w-4 bg-green-500 border-2 border-white rounded-full"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <a href="<?php echo e(route('profile.show', $friend->id)); ?>" class="block">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        <?php echo e($friend->full_name ?? $friend->username); ?>

                                                    </p>
                                                    <p class="text-sm text-gray-500 truncate">
                                                        <?php echo e($friend->is_online ? 'Online' : 'Offline'); ?>

                                                    </p>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 flex space-x-2">
                                                <a href="<?php echo e(route('messaging.index')); ?>?user=<?php echo e($friend->id); ?>" 
                                                   class="text-blue-600 hover:text-blue-800" 
                                                   title="Message">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                                <button onclick="removeFriend(<?php echo e($friend->id); ?>)" 
                                                        class="text-red-600 hover:text-red-800" 
                                                        title="Remove Friend">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-user-friends text-gray-400 text-6xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No friends yet</p>
                                <p class="text-gray-400 text-sm mt-2">Start adding friends to see them here!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pending Requests Tab -->
                    <div id="content-pending" class="tab-content hidden">
                        <?php if($pendingRequests->count() > 0): ?>
                            <div class="space-y-4">
                                <?php $__currentLoopData = $pendingRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    <?php if($request->avatar_url): ?>
                                                        <img src="<?php echo e(asset($request->avatar_url)); ?>" alt="<?php echo e($request->full_name); ?>" class="h-12 w-12 rounded-full object-cover">
                                                    <?php else: ?>
                                                        <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                            <?php echo e(strtoupper(substr($request->full_name ?? $request->username ?? 'U', 0, 1))); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <a href="<?php echo e(route('profile.show', $request->id)); ?>" class="block">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            <?php echo e($request->full_name ?? $request->username); ?>

                                                        </p>
                                                        <p class="text-sm text-gray-500">Wants to be your friend</p>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="acceptFriend(<?php echo e($request->id); ?>)" 
                                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                                    Accept
                                                </button>
                                                <button onclick="declineFriend(<?php echo e($request->id); ?>)" 
                                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 text-sm">
                                                    Decline
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-clock text-gray-400 text-6xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No pending requests</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sent Requests Tab -->
                    <div id="content-sent" class="tab-content hidden">
                        <?php if($sentRequests->count() > 0): ?>
                            <div class="space-y-4">
                                <?php $__currentLoopData = $sentRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    <?php if($request->avatar_url): ?>
                                                        <img src="<?php echo e(asset($request->avatar_url)); ?>" alt="<?php echo e($request->full_name); ?>" class="h-12 w-12 rounded-full object-cover">
                                                    <?php else: ?>
                                                        <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                            <?php echo e(strtoupper(substr($request->full_name ?? $request->username ?? 'U', 0, 1))); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <a href="<?php echo e(route('profile.show', $request->id)); ?>" class="block">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            <?php echo e($request->full_name ?? $request->username); ?>

                                                        </p>
                                                        <p class="text-sm text-gray-500">Request sent</p>
                                                    </a>
                                                </div>
                                            </div>
                                            <button onclick="cancelFriendRequest(<?php echo e($request->id); ?>)" 
                                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-paper-plane text-gray-400 text-6xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No sent requests</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            
            // Show selected tab content
            const content = document.getElementById('content-' + tabName);
            if (content) {
                content.classList.remove('hidden');
            }
            
            // Add active class to selected tab
            const tab = document.getElementById('tab-' + tabName);
            if (tab) {
                tab.classList.add('active', 'border-blue-500', 'text-blue-600');
                tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            }
        }

        async function acceptFriend(friendId) {
            try {
                const response = await fetch('/api/friends/accept', {
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
                    alert('Friend request accepted!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to accept friend request');
                }
            } catch (error) {
                console.error('Error accepting friend:', error);
                alert('Failed to accept friend request');
            }
        }

        async function declineFriend(friendId) {
            if (!confirm('Are you sure you want to decline this friend request?')) {
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
                    alert('Friend request declined');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to decline friend request');
                }
            } catch (error) {
                console.error('Error declining friend:', error);
                alert('Failed to decline friend request');
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

        async function cancelFriendRequest(friendId) {
            if (!confirm('Are you sure you want to cancel this friend request?')) {
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
                    alert('Friend request cancelled');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to cancel friend request');
                }
            } catch (error) {
                console.error('Error cancelling friend request:', error);
                alert('Failed to cancel friend request');
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

<?php /**PATH C:\xampp\htdocs\Material\resources\views/friends/index.blade.php ENDPATH**/ ?>