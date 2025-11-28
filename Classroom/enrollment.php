<?php
// --- PHP Logic: Read and Update Data ---
$data_file = 'available_lessons.json';
$json_data = file_get_contents($data_file);
$lessons = json_decode($json_data, true);

$message = '';

// Handle Enrollment Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_id'])) {
    $enroll_id = $_POST['enroll_id'];
    
    foreach ($lessons as &$lesson) {
        if ($lesson['lesson_id'] == $enroll_id) {
            if (!$lesson['is_mandatory']) {
                $lesson['enrolled'] = true;
                $message = "Success! You have enrolled in: " . $lesson['title'];
            }
            break;
        }
    }
    // Save changes back to JSON mock DB
    file_put_contents($data_file, json_encode($lessons, JSON_PRETTY_PRINT));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment - CompuPlay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* CompuPlay Theme */
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 30px auto; padding: 20px; }
        
        /* Success Message */
        .alert { padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px; }

        /* Card Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        
        .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; border-top: 4px solid #2454FF; }
        .card.mandatory { border-top-color: #E92222; } /* Red top for mandatory */
        
        .card h3 { margin-top: 0; color: #333; font-size: 1.2em; }
        .topic { color: #666; font-size: 0.9em; margin-bottom: 10px; display: block; }
        .duration { font-weight: bold; color: #5FAD56; font-size: 0.9em; }
        
        .badge { 
            position: absolute; top: 15px; right: 15px; 
            padding: 4px 8px; border-radius: 12px; font-size: 0.75em; font-weight: bold;
            color: white;
        }
        .bg-green { background-color: #5FAD56; }
        .bg-blue { background-color: #2454FF; }
        .bg-grey { background-color: #999; }

        /* Enroll Button */
        .btn-enroll { 
            display: block; width: 100%; padding: 10px; margin-top: 15px;
            background-color: #2454FF; color: white; border: none; border-radius: 4px;
            cursor: pointer; text-align: center; text-decoration: none; font-weight: bold;
        }
        .btn-enroll:hover { background-color: #1a3aab; }
        .btn-disabled { background-color: #ccc; cursor: default; color: #666; }
        
    </style>
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
                            <a href="../Dashboard/dashboard.php" class="flex items-center space-x-3">
                                <img src="../assets/images/LOGOCompuPlay.png" alt="Logo" class="h-10 w-auto">
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <a href="../Dashboard/dashboard.php" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Dashboard
                            </a>
                            <a href="enrollment.php" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
                                Classroom
                            </a>
                            <a href="../Forum/forum.html" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Forum
                            </a>
                            <a href="../Lesson/lesson.html" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Lessons
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
                                <div id="userName">User</div>
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
                    <a href="../Dashboard/dashboard.php" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                        Dashboard
                    </a>
                    <a href="enrollment.php" class="block pl-3 pr-4 py-2 border-l-4 border-blue-500 text-base font-medium text-blue-700 bg-blue-50 focus:outline-none focus:text-blue-800 focus:bg-blue-100 focus:border-blue-700 transition duration-150 ease-in-out">
                        Classroom
                    </a>
                    <a href="../Forum/forum.html" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                        Forum
                    </a>
                    <a href="../Lesson/lesson.html" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                        Lessons
                    </a>
                </div>

                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800" id="mobileUserName">User</div>
                        <div class="font-medium text-sm text-gray-500" id="mobileUserEmail"></div>
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
                <h2 class="font-semibold text-2xl leading-tight" style="color: #3E3E3E;">
                    Course Catalog
                </h2>
                <p class="text-sm mt-1" style="color: #969696;">Explore and enroll in extra learning materials.</p>
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-8">
            <div class="container">
                
                <?php if ($message): ?>
                    <div class="alert"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="grid">
                    <?php foreach ($lessons as $lesson): ?>
                        <div class="card <?php echo $lesson['is_mandatory'] ? 'mandatory' : ''; ?>">
                            
                            <!-- Status Badge -->
                            <?php if ($lesson['enrolled']): ?>
                                <span class="badge bg-green">Enrolled</span>
                            <?php elseif ($lesson['is_mandatory']): ?>
                                <span class="badge bg-grey">Mandatory</span>
                            <?php else: ?>
                                <span class="badge bg-blue">Optional</span>
                            <?php endif; ?>

                            <h3><?php echo $lesson['title']; ?></h3>
                            <span class="topic">Topic: <?php echo $lesson['topic']; ?></span>
                            <div class="duration"><i class="far fa-clock"></i> <?php echo $lesson['duration']; ?> mins</div>
                            
                            <!-- Action Button -->
                            <form method="POST">
                                <input type="hidden" name="enroll_id" value="<?php echo $lesson['lesson_id']; ?>">
                                
                                <?php if ($lesson['enrolled']): ?>
                                    <button type="button" class="btn-enroll btn-disabled" disabled>Already Enrolled</button>
                                <?php else: ?>
                                    <button type="submit" class="btn-enroll">Enroll Now</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', () => {
            const userName = sessionStorage.getItem('userName') || sessionStorage.getItem('userEmail') || 'User';
            const userEmail = sessionStorage.getItem('userEmail') || '';
            
            const userNameElement = document.getElementById('userName');
            if (userNameElement) userNameElement.textContent = userName;
            
            const mobileUserName = document.getElementById('mobileUserName');
            if (mobileUserName) mobileUserName.textContent = userName;
            
            const mobileUserEmail = document.getElementById('mobileUserEmail');
            if (mobileUserEmail) mobileUserEmail.textContent = userEmail;
            
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