<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>">
        <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Tailwind CSS CDN (fallback if Vite not available) -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Alpine.js for interactive components -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        <!-- Scripts -->
        <?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
            <?php echo app('Illuminate\Foundation\Vite')(['resources/sass/app.scss', 'resources/js/app.js']); ?>
        <?php endif; ?>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen bg-gray-50">
            <?php
                $currentUser = session('user_id') ? \App\Models\User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
            ?>
            <?php echo $__env->make('layouts.navigation', ['currentUser' => $currentUser], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Page Heading -->
            <?php if(isset($header)): ?>
                <header class="bg-white border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <?php echo e($header); ?>

                    </div>
                </header>
            <?php endif; ?>

            <!-- Page Content -->
            <main>
                <?php echo e($slot); ?>

            </main>
        </div>
        
        <!-- Ketupat Chatbot Widget -->
        <?php
            $currentUser = session('user_id') ? \App\Models\User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
            $chatbotEnabled = $currentUser ? ($currentUser->chatbot_enabled ?? true) : true;
        ?>
        <?php if($chatbotEnabled): ?>
            <?php echo $__env->make('components.chatbot-widget', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
        
        <!-- Navigation JavaScript -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Notification button toggle
                const notificationBtn = document.getElementById('notificationBtn');
                const notificationMenu = document.getElementById('notificationMenu');
                
                if (notificationBtn && notificationMenu) {
                    notificationBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        notificationMenu.classList.toggle('hidden');
                        if (!notificationMenu.classList.contains('hidden')) {
                            loadNotifications();
                        }
                    });
                    
                    // Close notification menu when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!notificationMenu.contains(e.target) && !notificationBtn.contains(e.target)) {
                            notificationMenu.classList.add('hidden');
                        }
                    });
                }
                
                // Load notifications on page load
                loadNotifications();
                // Refresh notifications every 30 seconds
                setInterval(loadNotifications, 30000);
            });

            async function loadNotifications() {
                try {
                    const response = await fetch('/api/notifications?unread_only=true&limit=10', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'include'
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    
                    if (data.status === 200) {
                        const notifications = data.data.notifications || [];
                        const unreadCount = data.data.unread_count || 0;
                        
                        // Update badge
                        const badge = document.getElementById('notificationBadge');
                        if (badge) {
                            if (unreadCount > 0) {
                                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                        
                        // Render notifications
                        renderNotifications(notifications);
                    }
                } catch (error) {
                    console.error('Error loading notifications:', error);
                }
            }

            function renderNotifications(notifications) {
                const container = document.getElementById('notificationList');
                if (!container) return;

                if (notifications.length === 0) {
                    container.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500 text-center">No notifications</div>';
                    return;
                }

                container.innerHTML = notifications.map(notif => {
                    const timeAgo = formatTimeAgo(notif.created_at);
                    let actionButtons = '';
                    
                    // Add action buttons for friend requests
                    if (notif.type === 'friend_request' && !notif.is_read) {
                        // Extract user_id from notification message or use related_id
                        // The related_id is the friend request ID, we'll fetch it to get user_id
                        actionButtons = `
                            <div class="mt-2 flex space-x-2">
                                <button onclick="handleFriendRequestFromNotification(${notif.related_id}, 'accept', ${notif.id})" 
                                        class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Accept
                                </button>
                                <button onclick="handleFriendRequestFromNotification(${notif.related_id}, 'decline', ${notif.id})" 
                                        class="px-3 py-1 text-xs bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                    Decline
                                </button>
                            </div>
                        `;
                    }
                    
                    return `
                        <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 ${!notif.is_read ? 'bg-blue-50' : ''}" 
                             onclick="${notif.type === 'friend_request' ? '' : 'markNotificationRead(' + notif.id + ')'}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">${escapeHtml(notif.title)}</p>
                                    <p class="text-sm text-gray-600 mt-1">${escapeHtml(notif.message)}</p>
                                    <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                                    ${actionButtons}
                                </div>
                                ${!notif.is_read ? '<div class="ml-2 h-2 w-2 bg-blue-600 rounded-full"></div>' : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            async function handleFriendRequestFromNotification(friendRequestId, action, notificationId) {
                try {
                    // Get the friend request details to find the user_id (the sender)
                    const friendRequestResponse = await fetch(`/api/friends/request/${friendRequestId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'include'
                    });

                    let friendId = null;
                    if (friendRequestResponse.ok) {
                        const friendData = await friendRequestResponse.json();
                        if (friendData.status === 200 && friendData.data) {
                            // user_id is the one who sent the request
                            friendId = friendData.data.user_id;
                        }
                    }

                    if (!friendId) {
                        alert('Could not find friend request details');
                        return;
                    }

                    // Call the appropriate endpoint
                    const endpoint = action === 'accept' ? '/api/friends/accept' : '/api/friends/remove';
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'include',
                        body: JSON.stringify({ 
                            friend_id: friendId
                        })
                    });

                    const data = await response.json();
                    
                    if (data.status === 200) {
                        // Mark notification as read
                        await markNotificationRead(notificationId);
                        // Reload notifications
                        loadNotifications();
                        if (action === 'accept') {
                            alert('Friend request accepted!');
                        } else {
                            alert('Friend request declined');
                        }
                    } else {
                        alert(data.message || 'Failed to ' + action + ' friend request');
                    }
                } catch (error) {
                    console.error('Error handling friend request:', error);
                    alert('Failed to ' + action + ' friend request');
                }
            }

            async function markNotificationRead(notificationId) {
                try {
                    await fetch(`/api/notifications/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'include'
                    });
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                }
            }

            function formatTimeAgo(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                const now = new Date();
                const diff = now - date;
                const seconds = Math.floor(diff / 1000);
                const minutes = Math.floor(seconds / 60);
                const hours = Math.floor(minutes / 60);
                const days = Math.floor(hours / 24);

                if (days > 0) return days + 'd ago';
                if (hours > 0) return hours + 'h ago';
                if (minutes > 0) return minutes + 'm ago';
                return 'Just now';
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        </script>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\Material\resources\views/layouts/app.blade.php ENDPATH**/ ?>