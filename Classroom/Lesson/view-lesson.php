<?php
require_once '../../config/database.php';

// Session is started by database.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in'])) {
    header('Location: ../../login.html');
    exit;
}

$lesson_id = $_GET['id'] ?? 0;

// Get user data
try {
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT id, username, email, full_name, role, avatar_url FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: ../../login.html');
        exit;
    }
} catch (Exception $e) {
    header('Location: ../../login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Lesson - CompuPlay</title>
    <link rel="stylesheet" href="../../Dashboard/CSS/dashboard.css">
    <link rel="stylesheet" href="CSS/lesson.css">
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
                        <div class="shrink-0 flex items-center">
                            <a href="../../Dashboard/dashboard.php" class="flex items-center space-x-3">
                                <img src="../../assets/images/LOGOCompuPlay.png" alt="Logo" class="h-10 w-auto">
                            </a>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <a href="../../Dashboard/dashboard.php" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700">Dashboard</a>
                            <a href="student-lessons.php" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900">Classroom</a>
                            <a href="../../Forum/forum.html" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700">Forum</a>
                        </div>
                    </div>
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <div class="relative">
                            <button id="userMenuBtn" class="inline-flex items-center px-4 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-lg text-gray-800 bg-white hover:bg-blue-50">
                                <div><?php echo htmlspecialchars($user['full_name'] ?? $user['email'] ?? 'User'); ?></div>
                                <svg class="fill-current h-4 w-4 ms-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="../../Forum/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-8 bg-gradient-to-b from-gray-50 to-white min-h-screen">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <a href="student-lessons.php" class="text-gray-600 hover:text-gray-900 font-medium mb-4 inline-block">
                        ← Back to Lessons
                    </a>
                </div>

                <div id="alertContainer"></div>
                <div id="lessonContent" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="text-center py-12">
                        <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500">Loading lesson...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="JS/view-lesson.js"></script>
    <script>
        // Pass lesson ID to JavaScript
        window.lessonId = <?php echo json_encode($lesson_id); ?>;
        
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', () => {
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
        });
    </script>
</body>
</html>

