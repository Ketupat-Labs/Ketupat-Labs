<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Details - Material Forum</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('Forum/CSS/forum.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('Forum/CSS/forum-detail.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('Forum/CSS/post-detail.css')); ?>">
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
                        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center space-x-3">
                            <img src="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>" alt="Logo" class="h-10 w-auto">
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <a href="<?php echo e(route('dashboard')); ?>"
                            class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Dashboard
                        </a>
                        <a href="<?php echo e(route('forum.index')); ?>"
                            class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
                            Forum
                        </a>
                        <a href="#"
                            class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Classroom
                        </a>
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
                                class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
                        </button>
                        <div id="notificationMenu"
                            class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200 max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                            </div>
                            <div id="notificationList" class="py-1">
                                <div class="px-4 py-3 text-sm text-gray-500 text-center">No notifications</div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Icon -->
                    <div class="relative">
                        <a href="<?php echo e(route('messaging.index')); ?>"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out relative">
                            <i class="fas fa-envelope text-lg"></i>
                            <span id="messageBadge"
                                class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
                        </a>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <button id="userMenuBtn"
                            class="inline-flex items-center px-4 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-lg text-gray-800 bg-white hover:bg-blue-50 hover:border-blue-300 focus:outline-none transition ease-in-out duration-150">
                            <div id="userName">User</div>
                            <svg class="fill-current h-4 w-4 ms-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div id="userMenu"
                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="<?php echo e(route('logout')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Out</a>
                        </div>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button id="mobileMenuBtn"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

                <!-- Mobile Navigation Menu -->
                <div id="mobileMenu" class="hidden sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <a href="<?php echo e(route('dashboard')); ?>"
                            class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                            Dashboard
                        </a>
                        <a href="<?php echo e(route('forum.index')); ?>"
                            class="block pl-3 pr-4 py-2 border-l-4 border-blue-500 text-base font-medium text-blue-700 bg-blue-50 focus:outline-none focus:text-blue-800 focus:bg-blue-100 focus:border-blue-700 transition duration-150 ease-in-out">
                            Forum
                        </a>
                        <a href="#"
                            class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                            Classroom
                        </a>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800" id="mobileUserName">User</div>
                            <div class="font-medium text-sm text-gray-500" id="mobileUserEmail"></div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <a href="#"
                                class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                                Profile
                            </a>
                            <a href="<?php echo e(route('logout')); ?>"
                                class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                                Log Out
                            </a>
                        </div>
                    </div>
                </div>
    </nav>

    <div class="reddit-container">

        <div class="forum-page-header">
            <a href="/forums" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            <div class="forum-page-header-content">
                <div class="forum-page-avatar" id="forumAvatar">
                    <span>?</span>
                </div>
                <div class="forum-page-info">
                    <div class="forum-page-title" id="forumName">Loading...</div>
                    <div class="forum-page-subtitle" id="forumSubtitle">Forum</div>
                </div>
                <div class="forum-header-actions">
                    <button class="btn-create-post-forum" id="btnCreatePostForForum" style="display: none;">
                        <i class="fas fa-plus"></i>
                        Create Post
                    </button>
                    <button class="btn-join" id="btnJoin" style="display: none;">
                        Join
                    </button>
                    <button class="btn-joined" id="btnJoined" style="display: none;">
                        Joined
                    </button>
                    <div class="more-menu-container">
                        <button class="btn-more-dots" id="btnMore">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div class="more-menu" id="moreMenu">
                            <div class="more-menu-item" id="muteOption">
                                <i class="fas fa-bell-slash"></i>
                                <span id="muteText">Mute Forum</span>
                            </div>
                            <div class="more-menu-item" id="favoriteOption">
                                <i class="far fa-bookmark"></i>
                                <span id="favoriteText">Add to Favorites</span>
                            </div>
                            <div class="more-menu-item" id="manageOption" style="display: none;">
                                <i class="fas fa-cog"></i>
                                <span>Manage Forum</span>
                            </div>
                            <div class="more-menu-item" id="leaveOption" style="color: #ff4500;">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Leave Forum</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="reddit-main-forum">
            <div class="reddit-main-forum-content">
                <div class="sort-controls-forum">
                    <select id="sortPosts">
                        <option value="recent">Hot</option>
                        <option value="popular">New</option>
                        <option value="top">Top</option>
                        <option value="rising">Rising</option>
                    </select>
                    <div class="view-toggle">
                        <button class="view-toggle-btn" title="Card View">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                </div>

                <main class="reddit-content" id="postsContent">
                </main>
            </div>

            <aside class="reddit-main-forum-sidebar">
                <div class="forum-sidebar-card">
                    <div class="forum-sidebar-card-header">
                        ABOUT COMMUNITY
                    </div>
                    <div class="forum-sidebar-card-body">
                        <div class="forum-description" id="forumDescription">
                            No description available.
                        </div>

                        <div class="forum-meta">
                            <div class="forum-meta-item">
                                <i class="fas fa-home"></i>
                                <span>Created <span id="createdDate">-</span></span>
                            </div>
                            <div class="forum-meta-item">
                                <i class="fas fa-globe"></i>
                                <span id="forumVisibility">Public</span>
                            </div>
                        </div>

                        <div class="forum-stats-grid">
                            <div class="forum-stat-big">
                                <div class="forum-stat-value" id="memberCount">0</div>
                                <div class="forum-stat-label">Members</div>
                            </div>
                            <div class="forum-stat-big">
                                <div class="forum-stat-value" id="postCount">0</div>
                                <div class="forum-stat-label">Online</div>
                            </div>
                        </div>

                        <button class="btn-message-mods" style="margin-top: 16px;">
                            <i class="fas fa-comment"></i>
                            Message Mods
                        </button>
                    </div>
                </div>

            </aside>
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

        <script src="<?php echo e(asset('Forum/JS/forum-detail.js')); ?>"></script>
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
                    });

                    // Close menu when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                            userMenu.classList.add('hidden');
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
            });
        </script>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\Material\resources\views/forum/forum-detail.blade.php ENDPATH**/ ?>