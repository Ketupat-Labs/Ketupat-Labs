<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Post Details - Material Forum</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">
    <link rel="stylesheet" href="{{ asset('Forum/CSS/forum.css') }}">
    <link rel="stylesheet" href="{{ asset('Forum/CSS/forum-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('Forum/CSS/post-detail.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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

                <!-- Settings Dropdown (without search bar) -->
                <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-3">
                    <!-- Notification Icon -->
                    <div class="relative">
                        <button id="notificationBtn"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out relative">
                            <i class="fas fa-bell text-lg"></i>
                            <span id="notificationBadge"
                                class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
                        </button>
                        <div id="notificationMenu"
                            class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200 max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
                            </div>
                            <div id="notificationList" class="py-1">
                                <div class="px-4 py-3 text-sm text-gray-500 text-center">Tiada notifikasi</div>
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
                            class="inline-flex items-center px-4 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-lg text-gray-800 bg-white hover:bg-blue-50 hover:border-blue-300 focus:outline-none transition ease-in-out duration-150">
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
                            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Keluar</button>
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
                    <a href="{{ route('settings.index') }}"
                        class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                        Tetapan
                    </a>
                    <a href="{{ route('settings.index') }}"
                        class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                        Tetapan
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="w-full text-left block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
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
                        <i class="fas fa-plus-circle"></i> Create Forum
                    </button>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title">My Forums</h3>
                    <div class="filter-list" id="forumsList">
                        <!-- Forums will be loaded here -->
                    </div>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title">Popular Tags</h3>
                    <div class="tag-cloud" id="tagCloud">
                    </div>
                </div>
            </aside>

            <main class="reddit-content">
                <div class="post-detail-content" id="postDetailContent">
                    <!-- Post content will be loaded here -->
                </div>
            </main>

            <aside class="reddit-main-forum-sidebar">
                <div class="forum-sidebar-card">
                    <div class="forum-sidebar-card-header">
                        ABOUT COMMUNITY
                    </div>
                    <div class="forum-sidebar-card-body" id="aboutCommunity">
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <script src="{{ asset('Forum/JS/post-detail.js') }}"></script>
    <script>
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', () => {
            // Load user info from sessionStorage
            const userName = sessionStorage.getItem('userName') || sessionStorage.getItem('userEmail') || 'User';
            const userEmail = sessionStorage.getItem('userEmail') || '';

            // Update user name in navigation
            const userNameElement = document.getElementById('userName');
            if (userNameElement) {
                userNameElement.textContent = userName;
            }

            const mobileUserName = document.getElementById('mobileUserName');
            if (mobileUserName) {
                mobileUserName.textContent = userName;
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

            // Notification menu toggle
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationMenu = document.getElementById('notificationMenu');

            if (notificationBtn && notificationMenu) {
                notificationBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notificationMenu.classList.toggle('hidden');
                    // Close user menu when opening notification menu
                    if (userMenu) {
                        userMenu.classList.add('hidden');
                    }
                });

                // Close notification menu when clicking outside
                document.addEventListener('click', (e) => {
                    if (!notificationBtn.contains(e.target) && !notificationMenu.contains(e.target)) {
                        notificationMenu.classList.add('hidden');
                    }
                });
            }

            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Load notifications
            loadNotifications();
        });

        // Load notifications function
        async function loadNotifications() {
            try {
                const response = await fetch('/api/notifications/', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'include',
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.status === 200 && data.data && data.data.notifications) {
                    const notifications = data.data.notifications;
                    const unreadCount = notifications.filter(n => !n.is_read).length;

                    // Update notification badge
                    const notificationBadge = document.getElementById('notificationBadge');
                    if (notificationBadge) {
                        if (unreadCount > 0) {
                            notificationBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                            notificationBadge.classList.remove('hidden');
                        } else {
                            notificationBadge.classList.add('hidden');
                        }
                    }

                    // Update notification list
                    const notificationList = document.getElementById('notificationList');
                    if (notificationList) {
                        if (notifications.length === 0) {
                            notificationList.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500 text-center">Tiada notifikasi</div>';
                        } else {
                            notificationList.innerHTML = notifications.slice(0, 10).map(notification => `
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 ${!notification.is_read ? 'bg-blue-50' : ''}" onclick="handleNotificationClick(${notification.id}, '${notification.related_type}', ${notification.related_id || 'null'}); return false;">
                                    <div class="text-sm font-medium text-gray-900">${escapeHtml(notification.title)}</div>
                                    <div class="text-sm text-gray-500 mt-1">${escapeHtml(notification.message)}</div>
                                    <div class="text-xs text-gray-400 mt-1">${formatNotificationTime(notification.created_at)}</div>
                                </a>
                            `).join('');
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }

        function handleNotificationClick(notificationId, relatedType, relatedId) {
            // Mark as read
            fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'include',
            });

            // Navigate based on type
            if (relatedType === 'post' && relatedId) {
                window.location.href = `/forum/post/${relatedId}`;
            } else if (relatedType === 'comment' && relatedId) {
                // Could navigate to comment detail or post with comment highlighted
                window.location.href = `/forum/post/${relatedId}`;
            } else if (relatedType === 'forum_post' && relatedId) {
                window.location.href = `/forum/post/${relatedId}`;
            } else if (relatedType === 'mention' && relatedId) {
                window.location.href = `/forum/post/${relatedId}`;
            }
        }

        function formatNotificationTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Baru sahaja';
            if (diffMins < 60) return `${diffMins} minit yang lalu`;
            if (diffHours < 24) return `${diffHours} jam yang lalu`;
            if (diffDays < 7) return `${diffDays} hari yang lalu`;
            return date.toLocaleDateString('ms-MY');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>
