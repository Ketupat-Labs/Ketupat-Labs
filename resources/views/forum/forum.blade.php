<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forum - Platform Pembelajaran Material</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">
    <link rel="stylesheet" href="{{ asset('Forum/CSS/forum.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Laravel Echo and Pusher for WebSocket -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.umd.min.js"></script>
    
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
    
    <script>
        // Wait for Echo to be available
        (function() {
            function checkEcho() {
                if (typeof window.Echo !== 'undefined') {
                    window.dispatchEvent(new Event('echoReady'));
                    return true;
                } else if (typeof Echo !== 'undefined') {
                    window.Echo = Echo;
                    window.dispatchEvent(new Event('echoReady'));
                    return true;
                }
                return false;
            }
            
            if (!checkEcho()) {
                let attempts = 0;
                const maxAttempts = 20;
                const checkInterval = setInterval(() => {
                    attempts++;
                    if (checkEcho() || attempts >= maxAttempts) {
                        clearInterval(checkInterval);
                    }
                }, 100);
            }
        })();
    </script>
</head>

<body>
    <!-- Dashboard-style Navigation -->
    <nav class="bg-white border-b-2 border-blue-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center space-x-3">
                            <img src="{{ asset('assets/images/LOGOCompuPlay.png') }}" alt="Logo" class="h-10 w-auto">
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Papan Pemuka
                        </a>
                        <a href="{{ route('forum.index') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
                            Forum
                        </a>
                        <a href="{{ route('classrooms.index') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Kelas Saya
                        </a>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="hidden sm:flex sm:items-center sm:flex-1 sm:justify-center sm:mx-8">
                    <div class="w-full max-w-2xl">
                        <div class="search-container">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchForums" placeholder="Cari forum...">
                        </div>
                    </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-3">
                    <!-- Notification Icon -->
                    <div class="relative">
                        <button id="notificationBtn"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out relative">
                            <i class="fas fa-bell text-lg"></i>
                            <span id="notificationBadge"
                                class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 min-w-[1.25rem] h-5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full z-10"></span>
                        </button>
                        <div id="notificationMenu"
                            class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-900">Pemberitahuan</h3>
                            </div>
                            <div id="notificationList" class="py-1 max-h-80 overflow-y-auto">
                                <div class="px-4 py-3 text-sm text-gray-500 text-center">Tiada pemberitahuan</div>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-200 bg-gray-50 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                    Lihat Semua Pemberitahuan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Message Icon -->
                    <div class="relative">
                        <a href="{{ route('messaging.index') }}"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out relative">
                            <i class="fas fa-envelope text-lg"></i>
                            <span id="messageBadge"
                                class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
                        </a>
                    </div>

                    <!-- Add Post Button -->
                    <a href="{{ route('forum.post.create') }}"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition ease-in-out duration-150">
                        <i class="fas fa-plus mr-2"></i> Tambah Post
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <button id="userMenuBtn"
                            class="inline-flex items-center px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-lg text-gray-800 bg-white hover:bg-blue-50 hover:border-blue-300 focus:outline-none transition ease-in-out duration-150 gap-2">
                            <div id="userAvatarContainer" class="flex-shrink-0">
                                <img id="userAvatarImg" src="" alt="User" class="h-8 w-8 rounded-full object-cover border-2 border-gray-200 hidden">
                                <div id="userAvatarPlaceholder" class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-sm border-2 border-gray-200">
                                    <span id="userAvatarInitial">U</span>
                                </div>
                            </div>
                            <div id="userName">User</div>
                            <svg class="fill-current h-4 w-4 ms-1" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div id="userMenu"
                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <a href="#" onclick="event.preventDefault(); const userId = sessionStorage.getItem('userId'); if (userId) { window.location.href = '/profile/' + userId; } else { window.location.href = '/profile'; } return false;" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                            <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Tetapan</a>
                            <form action="{{ route('logout') }}" method="POST" class="block w-full">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button id="mobileMenuBtn"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobileMenu" class="hidden sm:hidden">
            <!-- Mobile Search Bar -->
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchForumsMobile" placeholder="Cari forum...">
                </div>
            </div>

            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('dashboard') }}"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                    Papan Pemuka
                </a>
                <a href="{{ route('forum.index') }}"
                    class="block pl-3 pr-4 py-2 border-l-4 border-blue-500 text-base font-medium text-blue-700 bg-blue-50 focus:outline-none focus:text-blue-800 focus:bg-blue-100 focus:border-blue-700 transition duration-150 ease-in-out">
                    Forum
                </a>
                <a href="{{ route('classrooms.index') }}"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                    Kelas Saya
                </a>
            </div>

            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800" id="mobileUserName">User</div>
                    <div class="font-medium text-sm text-gray-500" id="mobileUserEmail"></div>
                </div>

                <div class="mt-3 space-y-1">
                    <a href="#" onclick="event.preventDefault(); const userId = sessionStorage.getItem('userId'); if (userId) { window.location.href = '/profile/' + userId; } else { window.location.href = '/profile'; } return false;"
                        class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                        Profil
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="block w-full">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                            Log Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="reddit-container">
        <div class="reddit-main">
            <aside class="reddit-sidebar">
                <div class="sidebar-section">
                    <button class="btn-create-forum" id="btnCreateForum">
                        <i class="fas fa-plus-circle"></i> Cipta Forum
                    </button>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title">Forum Saya</h3>
                    <div class="filter-list" id="forumsList">
                        <!-- Forums will be loaded here -->
                    </div>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title">Tag Popular</h3>
                    <div class="tag-cloud" id="tagCloud">
                    </div>
                </div>

                <div class="sidebar-section">
                    <div class="sort-controls">
                        <label for="sortPosts" class="sort-label">Susun mengikut:</label>
                        <select id="sortPosts" class="sort-select">
                            <option value="recent">Terbaru</option>
                            <option value="popular">Paling Popular</option>
                        </select>
                    </div>
                </div>
            </aside>

            <main class="reddit-content" id="forumsContent">
            </main>

            <aside class="reddit-sidebar-right">
                <div class="sidebar-section">
                    <div class="sidebar-header">
                        <h3 class="sidebar-title">Post Terkini</h3>
                        <button class="btn-clear-recent" onclick="clearRecentPosts()" title="Kosongkan">
                            Kosongkan
                        </button>
                    </div>
                    <div class="recent-posts-list" id="recentPostsList">
                        <p style="padding: 16px; color: #878a8c; font-size: 12px; text-align: center;">Tiada post terkini
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div class="modal" id="createForumModal">
        <div class="modal-content large">
            <div class="modal-header">
                <h2>Cipta Forum Baharu</h2>
                <button class="modal-close" id="closeForumModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="createForumForm">
                    <div class="form-group">
                        <label for="forumTitle">Tajuk Forum <span class="required">*</span></label>
                        <input type="text" id="forumTitle" required>
                    </div>

                    <div class="form-group">
                        <label for="forumDescription">Penerangan <span class="required">*</span></label>
                        <textarea id="forumDescription" rows="4" required
                            placeholder="Minimum 1 aksara"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="forumTags">Tag</label>
                            <input type="text" id="forumTags" placeholder="Pisahkan dengan koma">
                        </div>
                        <div class="form-group">
                            <label for="forumVisibility">Keterlihatan</label>
                            <select id="forumVisibility">
                                <option value="public">Awam</option>
                                <option value="class">Kelas Sahaja</option>
                                <option value="specific">Ahli Tertentu</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="forumStartDate">Tarikh Mula (Pilihan)</label>
                            <input type="datetime-local" id="forumStartDate">
                        </div>
                        <div class="form-group">
                            <label for="forumEndDate">Tarikh Tamat (Pilihan)</label>
                            <input type="datetime-local" id="forumEndDate">
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" id="cancelForumModal">Batal</button>
                        <button type="submit" class="btn-primary">Cipta Forum</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="postDetailModal">
        <div class="modal-content large">
            <div class="modal-header">
                <button class="modal-close" id="closePostModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="postDetailContent">
            </div>
        </div>
    </div>

    <script src="{{ asset('Forum/JS/forum.js') }}"></script>
    <script>
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', async () => {
            // Load user info from sessionStorage
            const userName = sessionStorage.getItem('userName') || sessionStorage.getItem('userEmail') || 'User';
            const userEmail = sessionStorage.getItem('userEmail') || '';
            let userAvatar = sessionStorage.getItem('userAvatar') || '';

            // If no avatar in sessionStorage, try to fetch from API
            if (!userAvatar) {
                try {
                    const response = await fetch('/api/auth/me', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        credentials: 'include',
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.status === 200 && data.data) {
                            if (data.data.avatar_url) {
                                userAvatar = data.data.avatar_url;
                                sessionStorage.setItem('userAvatar', userAvatar);
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error loading user avatar:', error);
                }
            }

            // Update user name in navigation
            const userNameElement = document.getElementById('userName');
            if (userNameElement) {
                userNameElement.textContent = userName;
            }

            const mobileUserName = document.getElementById('mobileUserName');
            if (mobileUserName) {
                mobileUserName.textContent = userName;
            }

            // Update user avatar
            const userAvatarImg = document.getElementById('userAvatarImg');
            const userAvatarPlaceholder = document.getElementById('userAvatarPlaceholder');
            const userAvatarInitial = document.getElementById('userAvatarInitial');
            
            if (userAvatarImg && userAvatarPlaceholder && userAvatarInitial) {
                if (userAvatar) {
                    // Show avatar image
                    // Handle both absolute URLs and relative paths
                    if (userAvatar.startsWith('http://') || userAvatar.startsWith('https://')) {
                        userAvatarImg.src = userAvatar;
                    } else if (userAvatar.startsWith('/')) {
                        userAvatarImg.src = userAvatar;
                    } else {
                        userAvatarImg.src = `{{ asset('') }}${userAvatar}`;
                    }
                    userAvatarImg.alt = userName || 'User';
                    userAvatarImg.classList.remove('hidden');
                    userAvatarPlaceholder.classList.add('hidden');
                } else {
                    // Show placeholder with initial
                    userAvatarImg.classList.add('hidden');
                    userAvatarPlaceholder.classList.remove('hidden');
                    const initial = userName ? userName.charAt(0).toUpperCase() : 'U';
                    userAvatarInitial.textContent = initial;
                }
            }

            const mobileUserEmail = document.getElementById('mobileUserEmail');
            if (mobileUserEmail) {
                mobileUserEmail.textContent = userEmail;
            }

            // User menu toggle
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userMenu = document.getElementById('userMenu');

            if (userMenuBtn && userMenu) {
                userMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userMenu.classList.toggle('hidden');
                    // Close notification menu when opening user menu
                    const notificationMenu = document.getElementById('notificationMenu');
                    if (notificationMenu) {
                        notificationMenu.classList.add('hidden');
                    }
                });

                // Close menu when clicking outside
                document.addEventListener('click', (e) => {
                    if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                        userMenu.classList.add('hidden');
                    }
                });
            }

            // Notification functionality - same as dashboard
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
                    
                    // Close user menu when opening notification menu
                    if (userMenu) {
                        userMenu.classList.add('hidden');
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
            setInterval(() => {
                updateUnreadCount();
            }, 30000);
            
            // Initialize WebSocket connection for real-time notifications
            // Wait for Echo library to load (it's loaded via CDN)
            let echoCheckAttempts = 0;
            const maxEchoChecks = 50; // Check for up to 5 seconds (50 * 100ms)
            
            function waitForEcho() {
                if (window.Echo || (typeof Echo !== 'undefined' && typeof Echo === 'function')) {
                    // Echo is available, initialize WebSocket
                    initializeNotificationWebSocket();
                } else if (echoCheckAttempts < maxEchoChecks) {
                    // Keep checking
                    echoCheckAttempts++;
                    setTimeout(waitForEcho, 100);
                } else {
                    // Echo library didn't load - this shouldn't happen but fall back gracefully
                    @if(config('app.debug'))
                        console.warn('Echo library not loaded after timeout. Check if CDN scripts are loading correctly.');
                    @endif
                    initializeNotificationWebSocket(); // Will fall back to polling inside
                }
            }
            
            // Also listen for the echoReady event (in case it fires)
            window.addEventListener('echoReady', () => {
                initializeNotificationWebSocket();
            }, { once: true });
            
            // Start checking for Echo
            waitForEcho();

            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Sync mobile and desktop search inputs
            const searchForums = document.getElementById('searchForums');
            const searchForumsMobile = document.getElementById('searchForumsMobile');

            if (searchForums && searchForumsMobile) {
                // Sync mobile to desktop
                searchForumsMobile.addEventListener('input', (e) => {
                    searchForums.value = e.target.value;
                    if (searchForums.dispatchEvent) {
                        searchForums.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });

                // Sync desktop to mobile
                searchForums.addEventListener('input', (e) => {
                    if (searchForumsMobile) {
                        searchForumsMobile.value = e.target.value;
                    }
                });
            }
        });
        
        // Notification functions - same as dashboard
        function initializeNotificationWebSocket() {
            @auth
            const userId = {{ auth()->id() }};
            const pusherAppKey = '{{ env('PUSHER_APP_KEY', '') }}';
            const pusherCluster = '{{ env('PUSHER_APP_CLUSTER', 'mt1') }}';
            
            if (!pusherAppKey || !userId) {
                @if(config('app.debug'))
                    console.log('Pusher configuration missing, using polling for notifications');
                @endif
                setInterval(loadNotifications, 30000);
                return;
            }
            
            let retryCount = 0;
            const maxRetries = 20;
            
            function initEcho() {
                try {
                    if (typeof window.Echo !== 'undefined') {
                        if (window.Echo.connector || window.Echo.options || window.Echo.private) {
                            @if(config('app.debug'))
                                console.log('Using existing Echo instance');
                            @endif
                            window.Echo.private(`user.${userId}`)
                                .listen('.notification.created', (data) => {
                                    @if(config('app.debug'))
                                        console.log('New notification received:', data);
                                    @endif
                                    handleNewNotification(data);
                                });
                            @if(config('app.debug'))
                                console.log('WebSocket connection established for real-time notifications');
                            @endif
                            return;
                        } else if (typeof window.Echo === 'function') {
                            @if(config('app.debug'))
                                console.log('Echo constructor found, creating new instance...');
                            @endif
                            if (typeof Pusher !== 'undefined') {
                                window.Pusher = Pusher;
                                Pusher.logToConsole = false;
                            }
                            window.Echo = new window.Echo({
                                broadcaster: 'pusher',
                                key: pusherAppKey,
                                cluster: pusherCluster,
                                forceTLS: true,
                                encrypted: true,
                                authEndpoint: '/broadcasting/auth',
                                auth: {
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                    },
                                },
                            });
                            
                            // Add connection error handling
                            if (window.Echo.connector && window.Echo.connector.pusher) {
                                window.Echo.connector.pusher.connection.bind('error', (err) => {
                                    @if(config('app.debug'))
                                        console.error('WebSocket connection error:', err);
                                    @endif
                                });
                                
                                window.Echo.connector.pusher.connection.bind('connected', () => {
                                    @if(config('app.debug'))
                                        console.log('WebSocket connected successfully');
                                    @endif
                                });
                                
                                window.Echo.connector.pusher.connection.bind('disconnected', () => {
                                    @if(config('app.debug'))
                                        console.log('WebSocket disconnected');
                                    @endif
                                });
                            }
                            
                            window.Echo.private(`user.${userId}`)
                                .listen('.notification.created', (data) => {
                                    @if(config('app.debug'))
                                        console.log('New notification received:', data);
                                    @endif
                                    handleNewNotification(data);
                                });
                            @if(config('app.debug'))
                                console.log('WebSocket connection established for real-time notifications');
                            @endif
                            return;
                        }
                    }
                    
                    if (retryCount < maxRetries) {
                        retryCount++;
                        setTimeout(initEcho, 200);
                        return;
                    }
                    
                    // Fall back to polling - this is expected if WebSocket server is not running
                    @if(config('app.debug'))
                        console.log('WebSocket connection unavailable, using polling for notifications');
                    @endif
                    setInterval(loadNotifications, 30000);
                } catch (error) {
                    @if(config('app.debug'))
                        console.error('Failed to initialize WebSocket:', error);
                    @endif
                    setInterval(loadNotifications, 30000);
                }
            }
            
            initEcho();
            @endauth
        }
        
        function handleNewNotification(data) {
            if (data.is_read) {
                return;
            }
            updateUnreadCount();
            const notificationMenu = document.getElementById('notificationMenu');
            const notificationList = document.getElementById('notificationList');
            if (notificationList && notificationMenu && !notificationMenu.classList.contains('hidden')) {
                loadNotifications();
            } else {
                prependNotification(data);
            }
            showNotificationToast(data.title, data.message);
        }
        
        function prependNotification(data) {
            const notificationList = document.getElementById('notificationList');
            if (!notificationList) return;
            const noNotificationsMsg = notificationList.querySelector('.text-center');
            if (noNotificationsMsg && noNotificationsMsg.textContent.includes('Tiada pemberitahuan')) {
                noNotificationsMsg.remove();
            }
            const notificationElement = createNotificationElement(data);
            notificationList.insertBefore(notificationElement, notificationList.firstChild);
            const notifications = notificationList.querySelectorAll('.notification-item');
            if (notifications.length > 10) {
                notifications[notifications.length - 1].remove();
            }
        }
        
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
        
        async function markAllNotificationsAsRead() {
            try {
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    badge.classList.add('hidden');
                    badge.textContent = '0';
                }
                const response = await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                if (response.ok) {
                    updateUnreadCount();
                    console.log('All notifications marked as read');
                } else {
                    updateUnreadCount();
                }
            } catch (error) {
                console.error('Error marking all notifications as read:', error);
                updateUnreadCount();
            }
        }
        
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
        
        function showNotificationToast(title, message) {
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
                    
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        if (unreadCount > 0) {
                            badge.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                    
                    renderNotifications(notifications);
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }
        
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
                    
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        if (unreadCount > 0) {
                            badge.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                    
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
                await markNotificationRead(notificationId);
                
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
                        const notificationMenu = document.getElementById('notificationMenu');
                        if (notificationMenu) {
                            notificationMenu.classList.add('hidden');
                        }
                        window.location.href = data.data.redirect_url;
                    }
                }
            } catch (error) {
                console.error('Error handling notification click:', error);
            }
        }

        async function markNotificationRead(notificationId) {
            try {
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
                
                if (response.ok) {
                    updateUnreadCount();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
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
</body>

</html>

