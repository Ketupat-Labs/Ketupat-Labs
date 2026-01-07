<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Tailwind CSS CDN (fallback if Vite not available) -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Custom styles for notification animations -->
        <style>
            @keyframes slide-in {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            .animate-slide-in {
                animation: slide-in 0.3s ease-out;
            }
        </style>
        
        <!-- Bootstrap CSS and JS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Alpine.js for interactive components -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        <!-- Laravel Echo and Pusher for WebSocket -->
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.umd.js"></script>
        <script>
            // Wait for Echo to be available and set up global reference
            (function() {
                function checkEcho() {
                    // The IIFE version exposes Echo on window
                    if (typeof window.Echo !== 'undefined') {
                        console.log('Laravel Echo loaded and available');
                        // Trigger custom event to notify that Echo is ready
                        window.dispatchEvent(new Event('echoReady'));
                        return true;
                    } else if (typeof Echo !== 'undefined') {
                        window.Echo = Echo;
                        console.log('Laravel Echo loaded and available');
                        window.dispatchEvent(new Event('echoReady'));
                        return true;
                    }
                    return false;
                }
                
                // Check immediately
                if (!checkEcho()) {
                    // If not ready, check periodically
                    let attempts = 0;
                    const maxAttempts = 20;
                    const checkInterval = setInterval(() => {
                        attempts++;
                        if (checkEcho() || attempts >= maxAttempts) {
                            clearInterval(checkInterval);
                            if (attempts >= maxAttempts) {
                                console.warn('Laravel Echo did not load after maximum attempts');
                            }
                        }
                    }, 100);
                }
            })();
        </script>
        
        <!-- Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/sass/app.scss', 'resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen bg-gray-50">
            @php
                $currentUser = session('user_id') ? \App\Models\User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
            @endphp
            @include('layouts.navigation', ['currentUser' => $currentUser])

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        
        <!-- Ketupat Chatbot Widget -->
        @php
            $currentUser = session('user_id') ? \App\Models\User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
            $chatbotEnabled = $currentUser ? ($currentUser->chatbot_enabled ?? true) : true;
        @endphp
        @if($chatbotEnabled)
            @include('components.chatbot-widget')
        @endif
        
        <!-- Navigation JavaScript -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Notification button toggle
                const notificationBtn = document.getElementById('notificationBtn');
                const notificationMenu = document.getElementById('notificationMenu');
                
                if (notificationBtn && notificationMenu) {
                    notificationBtn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const wasHidden = notificationMenu.classList.contains('hidden');
                        notificationMenu.classList.toggle('hidden');
                        
                        // If opening the menu, mark all notifications as read but keep them visible
                        if (wasHidden && !notificationMenu.classList.contains('hidden')) {
                            await markAllNotificationsAsRead();
                            // Load all notifications (including read ones) to show them
                            loadAllNotifications();
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
                
                // Periodic refresh of notification count (every 30 seconds) as fallback
                // This ensures badge stays in sync even if WebSocket fails
                setInterval(() => {
                    updateUnreadCount();
                }, 30000);
                
                // Initialize WebSocket connection for real-time notifications
                // Wait for Echo to be ready before initializing
                if (window.Echo || typeof Echo !== 'undefined') {
                    initializeNotificationWebSocket();
                } else {
                    // Wait for echoReady event
                    window.addEventListener('echoReady', () => {
                        initializeNotificationWebSocket();
                    }, { once: true });
                    
                    // Fallback: try after a delay
                    setTimeout(() => {
                        if (!window.Echo && typeof Echo === 'undefined') {
                            console.warn('Echo not available after delay, using polling fallback');
                        } else {
                            initializeNotificationWebSocket();
                        }
                    }, 1000);
                }
            });
            
            // Initialize Laravel Echo for real-time notifications
            function initializeNotificationWebSocket() {
                @auth
                const userId = {{ auth()->id() }};
                const pusherAppKey = '{{ env('PUSHER_APP_KEY', '') }}';
                
                if (!pusherAppKey || !userId) {
                    console.warn('Pusher configuration missing, real-time notifications disabled');
                    // Fallback to polling
                    setInterval(loadNotifications, 30000);
                    return;
                }
                
                // Wait for Echo instance to be available (it may already be initialized)
                let retryCount = 0;
                const maxRetries = 20;
                
                function initEcho() {
                    try {
                        // Check if Echo instance is already available
                        // window.Echo might already be an instance (not a constructor)
                        if (typeof window.Echo !== 'undefined') {
                            // Check if it's already an instance (has connector, options, etc.)
                            if (window.Echo.connector || window.Echo.options || window.Echo.private) {
                                console.log('Using existing Echo instance');
                                
                                // Listen for new notifications on user's private channel
                                window.Echo.private(`user.${userId}`)
                                    .listen('.notification.created', (data) => {
                                        console.log('New notification received:', data);
                                        handleNewNotification(data);
                                    });
                                
                                console.log('WebSocket connection established for real-time notifications');
                                return;
                            }
                            // If it's a constructor function, create new instance
                            else if (typeof window.Echo === 'function') {
                                console.log('Echo constructor found, creating new instance...');
                                
                                // Configure Pusher if not already configured
                                if (typeof Pusher !== 'undefined') {
                                    window.Pusher = Pusher;
                                    Pusher.logToConsole = false;
                                }
                                
                                // Create new Echo instance (will use Pusher config from echo.js)
                                if (typeof window.Echo === 'function') {
                                window.Echo = new window.Echo({
                                        broadcaster: 'pusher',
                                        key: pusherAppKey,
                                        cluster: '{{ env('PUSHER_APP_CLUSTER', 'mt1') }}',
                                        forceTLS: true,
                                        encrypted: true,
                                    authEndpoint: '/broadcasting/auth',
                                    auth: {
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        },
                                    },
                                });
                                }
                                
                                // Listen for new notifications
                                window.Echo.private(`user.${userId}`)
                                    .listen('.notification.created', (data) => {
                                        console.log('New notification received:', data);
                                        handleNewNotification(data);
                                    });
                                
                                console.log('WebSocket connection established for real-time notifications');
                                return;
                            }
                        }
                        
                        // If Echo is not available yet, retry
                        if (retryCount < maxRetries) {
                            retryCount++;
                            setTimeout(initEcho, 200);
                            return;
                        }
                        
                        // Fallback to polling if Echo never becomes available
                        console.warn('Echo not available after retries, falling back to polling');
                        setInterval(loadNotifications, 30000);
                    } catch (error) {
                        console.error('Failed to initialize WebSocket:', error);
                        // Fallback to polling
                        setInterval(loadNotifications, 30000);
                    }
                }
                
                // Start initialization
                initEcho();
                @endauth
            }
            
            // Handle new notification received via WebSocket
            function handleNewNotification(data) {
                // Only process if notification is unread
                if (data.is_read) {
                    return;
                }
                
                // Update badge count immediately (increment by 1)
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    const currentCount = parseInt(badge.textContent) || 0;
                    const newCount = currentCount + 1;
                    badge.textContent = newCount > 99 ? '99+' : newCount.toString();
                    badge.classList.remove('hidden');
                }
                
                // Also fetch latest count from server to ensure accuracy
                updateUnreadCount();
                
                // Add notification to the top of the list if dropdown is open
                const notificationMenu = document.getElementById('notificationMenu');
                const notificationList = document.getElementById('notificationList');
                if (notificationList && notificationMenu && !notificationMenu.classList.contains('hidden')) {
                    // Reload notifications to get the latest list
                    loadNotifications();
                } else {
                    // If dropdown is closed, just prepend the new notification to the list
                    prependNotification(data);
                }
                
                // Show a subtle notification toast
                showNotificationToast(data.title, data.message);
            }
            
            // Prepend a new notification to the list without reloading
            function prependNotification(data) {
                const notificationList = document.getElementById('notificationList');
                if (!notificationList) return;
                
                // Remove "no notifications" message if present
                const noNotificationsMsg = notificationList.querySelector('.text-center');
                if (noNotificationsMsg && noNotificationsMsg.textContent.includes('Tiada pemberitahuan')) {
                    noNotificationsMsg.remove();
                }
                
                // Create notification element
                const notificationElement = createNotificationElement(data);
                notificationList.insertBefore(notificationElement, notificationList.firstChild);
                
                // Limit to 10 notifications in dropdown
                const notifications = notificationList.querySelectorAll('.notification-item');
                if (notifications.length > 10) {
                    notifications[notifications.length - 1].remove();
                }
            }
            
            // Create a notification element from data
            function createNotificationElement(data) {
                const div = document.createElement('div');
                div.className = 'notification-item px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer bg-blue-50';
                div.onclick = () => handleNotificationClick(data.id, data.type, data.related_type || '', data.related_id || null);
                
                const timeAgo = formatTimeAgo(data.created_at);
                div.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(data.title)}</p>
                            <p class="text-sm text-gray-600 mt-1">${escapeHtml(data.message)}</p>
                            <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                        </div>
                    </div>
                `;
                
                return div;
            }
            
            // Mark all notifications as read
            async function markAllNotificationsAsRead() {
                try {
                    // Update badge immediately (optimistic update)
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        badge.classList.add('hidden');
                    }
                    
                    const response = await fetch('/api/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ mark_all: true }),
                        credentials: 'include'
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.status === 200) {
                            // Verify badge is hidden (in case of error, updateUnreadCount will fix it)
                            updateUnreadCount();
                            console.log('All notifications marked as read');
                        }
                    } else {
                        // If request failed, restore badge by fetching count
                        updateUnreadCount();
                    }
                } catch (error) {
                    console.error('Error marking all notifications as read:', error);
                    // On error, restore badge by fetching count
                    updateUnreadCount();
                }
            }
            
            // Update unread count from server
            async function updateUnreadCount() {
                try {
                    const response = await fetch('/api/notifications?unread_only=true&limit=1', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'include'
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.status === 200) {
                            const unreadCount = data.data.unread_count || 0;
                            const badge = document.getElementById('notificationBadge');
                            if (badge) {
                                if (unreadCount > 0) {
                                    badge.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
                                    badge.classList.remove('hidden');
                                } else {
                                    badge.classList.add('hidden');
                                }
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error updating unread count:', error);
                }
            }
            
            // Show a toast notification (optional visual feedback)
            function showNotificationToast(title, message) {
                // Create toast element
                const toast = document.createElement('div');
                toast.className = 'fixed top-20 right-4 bg-white border-l-4 border-blue-500 shadow-lg rounded-lg p-4 z-50 max-w-sm animate-slide-in';
                toast.innerHTML = `
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bell text-blue-500"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(title)}</p>
                            <p class="text-sm text-gray-500 mt-1">${escapeHtml(message)}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                document.body.appendChild(toast);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.style.opacity = '0';
                        toast.style.transition = 'opacity 0.3s';
                        setTimeout(() => toast.remove(), 300);
                    }
                }, 5000);
            }

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
                                badge.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
                                badge.classList.remove('hidden');
                                console.log('Notification badge updated:', unreadCount);
                            } else {
                                badge.classList.add('hidden');
                                console.log('No unread notifications, hiding badge');
                            }
                        } else {
                            console.error('Notification badge element not found!');
                        }
                        
                        // Render notifications
                        renderNotifications(notifications);
                    }
                } catch (error) {
                    console.error('Error loading notifications:', error);
                }
            }
            
            // Load all notifications (both read and unread) - used after marking all as read
            async function loadAllNotifications() {
                try {
                    const response = await fetch('/api/notifications?limit=10', {
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
                        
                        // Update badge (should be 0 after marking all as read)
                        const badge = document.getElementById('notificationBadge');
                        if (badge) {
                            if (unreadCount > 0) {
                                badge.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                        
                        // Render all notifications (they'll be marked as read but still visible)
                        renderNotifications(notifications);
                    }
                } catch (error) {
                    console.error('Error loading all notifications:', error);
                }
            }

            function renderNotifications(notifications) {
                const container = document.getElementById('notificationList');
                if (!container) return;

                if (notifications.length === 0) {
                    container.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500 text-center">Tiada pemberitahuan</div>';
                    return;
                }

                container.innerHTML = notifications.map(notif => {
                    const timeAgo = formatTimeAgo(notif.created_at);
                    
                    return `
                        <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer ${!notif.is_read ? 'bg-blue-50' : ''}" 
                             onclick="handleNotificationClick(${notif.id}, '${notif.type}', '${notif.related_type || ''}', ${notif.related_id || 'null'})">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">${escapeHtml(notif.title)}</p>
                                    <p class="text-sm text-gray-600 mt-1">${escapeHtml(notif.message)}</p>
                                    <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                                </div>
                                ${!notif.is_read ? '<div class="ml-2 h-2 w-2 bg-blue-600 rounded-full"></div>' : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            async function handleNotificationClick(notificationId, type, relatedType, relatedId) {
                try {
                    // Mark as read
                    await markNotificationRead(notificationId);
                    
                    // Get redirect URL from server
                    const response = await fetch(`/api/notifications/${notificationId}/redirect`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'include'
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.status === 200 && data.data.redirect_url) {
                            // Close notification menu
                            const notificationMenu = document.getElementById('notificationMenu');
                            if (notificationMenu) {
                                notificationMenu.classList.add('hidden');
                            }
                            // Redirect to the URL
                            window.location.href = data.data.redirect_url;
                        }
                    }
                } catch (error) {
                    console.error('Error handling notification click:', error);
                }
            }

            async function markNotificationRead(notificationId) {
                try {
                    // Optimistically update badge (decrement by 1)
                    const badge = document.getElementById('notificationBadge');
                    if (badge && !badge.classList.contains('hidden')) {
                        const currentCount = parseInt(badge.textContent) || 1;
                        const newCount = Math.max(0, currentCount - 1);
                        if (newCount > 0) {
                            badge.textContent = newCount > 99 ? '99+' : newCount.toString();
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                    
                    const response = await fetch(`/api/notifications/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'include'
                    });
                    
                    // Verify count with server after marking as read
                    if (response.ok) {
                        updateUnreadCount();
                    }
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                    // On error, restore badge by fetching count
                    updateUnreadCount();
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
        @stack('scripts')
    <!-- Achievement/Badge Earned Celebration Modal -->
    <div class="modal fade" id="badgeEarnedModal" tabindex="-1" aria-labelledby="badgeEarnedModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-2xl overflow-hidden border-0 shadow-2xl">
                <div class="modal-body p-0 text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="p-8">
                        <!-- Confetti Animation Container -->
                        <div id="confettiContainer" class="absolute inset-0 pointer-events-none overflow-hidden" style="z-index: 1;"></div>
                        
                        <!-- Content -->
                        <div style="position: relative; z-index: 2;">
                            <!-- Icon -->
                            <div class="mb-4">
                                <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center text-white bg-white bg-opacity-20">
                                    <i id="badgeEarnedIcon" class="fas fa-trophy text-5xl"></i>
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <h3 class="text-3xl font-bold text-white mb-2">Tahniah!</h3>
                            
                            <!-- Message -->
                            <p class="text-lg text-white mb-1">Anda telah memperoleh lencana</p>
                            <p id="badgeEarnedName" class="text-2xl font-bold text-white mb-4"></p>
                            <p id="badgeEarnedDescription" class="text-sm text-white opacity-90 mb-6"></p>
                            
                            <!-- Reward -->
                            <div class="bg-white bg-opacity-20 rounded-lg px-6 py-3 inline-block mb-6">
                                <p class="text-sm text-white opacity-90 mb-1">REWARD</p>
                                <p id="badgeEarnedXP" class="text-2xl font-bold text-white">0 XP</p>
                            </div>
                            
                            <!-- Button -->
                            <button type="button" class="px-8 py-3 bg-white text-purple-600 rounded-lg font-semibold hover:bg-gray-100 transition" data-bs-dismiss="modal">
                                Okay
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confetti Animation Script -->
    <script>
        // Function to create confetti
        function createConfetti(container) {
            const colors = ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F'];
            const confettiCount = 50;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'absolute';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = confetti.style.width;
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.top = '-10px';
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                confetti.style.opacity = Math.random();
                confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
                
                container.appendChild(confetti);
                
                // Animate confetti
                const duration = Math.random() * 3 + 2;
                const horizontalMovement = (Math.random() - 0.5) * 200;
                
                confetti.animate([
                    { transform: `translateY(0) translateX(0) rotate(0deg)`, opacity: 1 },
                    { transform: `translateY(600px) translateX(${horizontalMovement}px) rotate(720deg)`, opacity: 0 }
                ], {
                    duration: duration * 1000,
                    easing: 'cubic-bezier(0.5, 0, 0.5, 1)'
                }).onfinish = () => confetti.remove();
            }
        }
        
        // Function to show badge earned celebration
        function showBadgeEarnedCelebration(badgeName, badgeDescription, badgeIcon, badgeColor, xpReward) {
            // Set modal content
            document.getElementById('badgeEarnedName').textContent = badgeName;
            document.getElementById('badgeEarnedDescription').textContent = badgeDescription || '';
            document.getElementById('badgeEarnedIcon').className = (badgeIcon || 'fas fa-trophy') + ' text-5xl';
            document.getElementById('badgeEarnedXP').textContent = (xpReward || 0) + ' XP';
            
            // Create confetti
            const confettiContainer = document.getElementById('confettiContainer');
            confettiContainer.innerHTML = '';
            createConfetti(confettiContainer);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('badgeEarnedModal'));
            modal.show();
            
            // Create more confetti every 500ms for 3 seconds
            let confettiInterval = setInterval(() => {
                createConfetti(confettiContainer);
            }, 500);
            
            // Stop creating confetti after modal is closed
            document.getElementById('badgeEarnedModal').addEventListener('hidden.bs.modal', function() {
                clearInterval(confettiInterval);
                confettiContainer.innerHTML = '';
            }, { once: true });
        }
        
        // Listen for badge earned events (can be triggered from anywhere)
        window.addEventListener('badgeEarned', function(event) {
            const { badgeName, badgeDescription, badgeIcon, badgeColor, xpReward } = event.detail;
            showBadgeEarnedCelebration(badgeName, badgeDescription, badgeIcon, badgeColor, xpReward);
        });
        
        // Example: Check for newly earned badges on page load (optional)
        // This can be enhanced to check for badges earned in the current session
    </script>

    </body>
</html>
