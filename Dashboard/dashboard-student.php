<?php
// This file is included from dashboard.php
// Variables available: $user, $user_role_db
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="CSS/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white border-b-2 border-blue-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="dashboard.php" class="flex items-center space-x-3">
                                <img src="../assets/images/LOGOCompuPlay.png" alt="Logo" class="h-10 w-auto">
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <a href="dashboard.php" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
                                Dashboard
                            </a>
                            <a href="../Classroom/Lesson/student-lessons.php" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Classroom
                            </a>
                            <a href="../Forum/forum.html" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Forum
                            </a>
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-3">
                        <!-- Notification Icon -->
                        <div class="relative">
                            <button id="notificationBtn" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out relative">
                                <i class="fas fa-bell text-lg"></i>
                                <span id="notificationBadge" class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
                            </button>
                            <div id="notificationMenu" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200 max-h-96 overflow-y-auto">
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
                            <a href="../Messaging/messaging.html" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out relative">
                                <i class="fas fa-envelope text-lg"></i>
                                <span id="messageBadge" class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
                            </a>
                        </div>
                        
                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <button id="userMenuBtn" class="inline-flex items-center px-4 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-lg text-gray-800 bg-white hover:bg-blue-50 hover:border-blue-300 focus:outline-none transition ease-in-out duration-150">
                                <div id="userName"><?php echo htmlspecialchars($user['full_name'] ?? $user['email'] ?? 'User'); ?></div>
                                <svg class="fill-current h-4 w-4 ms-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="../Forum/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Out</a>
                            </div>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button id="mobileMenuBtn" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobileMenu" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="dashboard.php" class="block pl-3 pr-4 py-2 border-l-4 border-blue-500 text-base font-medium text-blue-700 bg-blue-50 focus:outline-none focus:text-blue-800 focus:bg-blue-100 focus:border-blue-700 transition duration-150 ease-in-out">
                        Dashboard
                    </a>
                    <a href="../Classroom/Lesson/student-lessons.php" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                        Classroom
                    </a>
                    <a href="../Forum/forum.html" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                        Forum
                    </a>
                </div>

                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800"><?php echo htmlspecialchars($user['full_name'] ?? $user['email'] ?? 'User'); ?></div>
                        <div class="font-medium text-sm text-gray-500"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <a href="#" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                            Profile
                        </a>
                        <a href="../Forum/logout.php" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                            Log Out
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-2xl leading-tight" style="color: #3E3E3E;">
                            Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? 'Student'); ?>!
                        </h2>
                        <p class="text-sm mt-1" style="color: #969696;">Continue your learning journey</p>
                    </div>
                    <?php if (($user['points'] ?? 0) > 0): ?>
                    <div class="text-white px-6 py-3 rounded-lg shadow-md" style="background: linear-gradient(to right, #5FAD56, #2454FF);">
                        <div class="text-xs font-medium opacity-90">Total Points</div>
                        <div class="text-2xl font-bold"><?php echo htmlspecialchars($user['points'] ?? 0); ?> XP</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-8 bg-gradient-to-b from-gray-50 to-white min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Quick Stats Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Available Lessons -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Available Lessons</p>
                                <p class="text-3xl font-bold text-blue-600 mt-2" id="availableLessonsCount">Loading...</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Lessons -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Completed Lessons</p>
                                <p class="text-3xl font-bold text-green-600 mt-2" id="completedLessonsCount">Loading...</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- My Submissions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">My Submissions</p>
                                <p class="text-3xl font-bold text-orange-600 mt-2" id="mySubmissionsCount">Loading...</p>
                            </div>
                            <div class="p-3 bg-orange-100 rounded-lg">
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Action Cards -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold mb-4" style="color: #3E3E3E;">Quick Access</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- View Lessons Card -->
                        <a href="../Classroom/Lesson/student-lessons.php" class="group bg-white rounded-xl shadow-sm border-2 border-transparent hover:shadow-lg transition-all duration-300 overflow-hidden">
                            <div class="p-6 text-white" style="background: linear-gradient(to bottom right, #2454FF, #1a3fcc);">
                                <div class="flex items-center justify-between mb-4">
                                    <svg class="w-12 h-12 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    <svg class="w-6 h-6 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                                <h4 class="text-xl font-bold mb-2">View Lessons</h4>
                                <p class="text-sm opacity-90">Browse and access all available lessons</p>
                            </div>
                        </a>

                        <!-- Take Quiz Card -->
                        <a href="#" class="group bg-white rounded-xl shadow-sm border-2 border-transparent hover:shadow-lg transition-all duration-300 overflow-hidden">
                            <div class="p-6 text-white" style="background: linear-gradient(to bottom right, #F26430, #c44d26);">
                                <div class="flex items-center justify-between mb-4">
                                    <svg class="w-12 h-12 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    <svg class="w-6 h-6 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                                <h4 class="text-xl font-bold mb-2">Take a Quiz</h4>
                                <p class="text-sm opacity-90">Test your knowledge and earn points</p>
                            </div>
                        </a>

                        <!-- Submit Assignment Card -->
                        <a href="#" class="group bg-white rounded-xl shadow-sm border-2 border-transparent hover:shadow-lg transition-all duration-300 overflow-hidden">
                            <div class="p-6 text-white" style="background: linear-gradient(to bottom right, #FFBA08, #d99e07);">
                                <div class="flex items-center justify-between mb-4">
                                    <svg class="w-12 h-12 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <svg class="w-6 h-6 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                                <h4 class="text-xl font-bold mb-2">Submit Assignment</h4>
                                <p class="text-sm opacity-90">Upload your practical work</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Lessons -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold" style="color: #3E3E3E;">Recent Lessons</h3>
                            <a href="../Classroom/Lesson/student-lessons.php" class="text-sm hover:underline font-medium" style="color: #2454FF;">View all</a>
                        </div>
                        <div class="space-y-4" id="recentLessonsSection">
                            <div class="text-center py-8 text-gray-500">
                                <p>No lessons available yet</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold mb-4" style="color: #3E3E3E;">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="../Classroom/Lesson/student-lessons.php" class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group" style="background: linear-gradient(to right, #2454FF, #1a3fcc);">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    <span class="font-semibold">Browse Lessons</span>
                                </div>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>

                            <a href="#" class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group" style="background: linear-gradient(to right, #F26430, #c44d26);">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    <span class="font-semibold">Take a Quiz</span>
                                </div>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>

                            <a href="#" class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group" style="background: linear-gradient(to right, #FFBA08, #d99e07);">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <span class="font-semibold">Submit Assignment</span>
                                </div>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="JS/dashboard.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.dashboardData = {
            userId: <?php echo json_encode($user['id']); ?>,
            userRole: <?php echo json_encode($user_role_db); ?>
        };

        // Navigation functionality
        document.addEventListener('DOMContentLoaded', () => {
            // User menu toggle
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userMenu = document.getElementById('userMenu');
            if (userMenuBtn && userMenu) {
                userMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userMenu.classList.toggle('hidden');
                });
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
                });
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

            // Load stats (placeholder values for now)
            document.getElementById('availableLessonsCount').textContent = '12';
            document.getElementById('completedLessonsCount').textContent = '5';
            document.getElementById('mySubmissionsCount').textContent = '3';
        });
    </script>
</body>
</html>
