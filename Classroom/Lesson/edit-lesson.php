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
    
    if (!$user || $user['role'] !== 'teacher') {
        header('Location: ../../Dashboard/dashboard.php');
        exit;
    }

    // Get lesson data
    if ($lesson_id) {
        $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$lesson_id, $user['id']]);
        $lesson = $stmt->fetch();
        
        if (!$lesson) {
            header('Location: manage-lessons.php');
            exit;
        }
    } else {
        header('Location: manage-lessons.php');
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
    <title>Edit Lesson - CompuPlay</title>
    <link rel="stylesheet" href="../../Dashboard/CSS/dashboard.css">
    <link rel="stylesheet" href="CSS/lesson.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation (same as create-lesson.php) -->
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
                            <a href="../../Dashboard/dashboard.php" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300">Dashboard</a>
                            <a href="manage-lessons.php" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900">Classroom</a>
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6 border-b-2 border-[#2454FF] pb-2">
                        <h2 class="text-2xl font-bold text-gray-800">Edit Lesson</h2>
                        <a href="manage-lessons.php" class="text-gray-600 hover:text-gray-900 font-medium">← Back to Lessons</a>
                    </div>

                    <div id="alertContainer"></div>

                    <form id="lessonForm" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" id="lesson_id" value="<?php echo htmlspecialchars($lesson['id']); ?>">
                        
                        <div>
                            <label for="title" class="block font-medium text-lg text-[#3E3E3E]">Lesson Title <span class="text-red-600">*</span></label>
                            <input type="text" name="title" id="title" required value="<?php echo htmlspecialchars($lesson['title']); ?>"
                                   class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50">
                        </div>

                        <div>
                            <label for="topic" class="block font-medium text-lg text-[#3E3E3E]">Module / Topic <span class="text-red-600">*</span></label>
                            <input type="text" name="topic" id="topic" list="topics" required value="<?php echo htmlspecialchars($lesson['topic']); ?>"
                                   class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50">
                            <datalist id="topics">
                                <option value="HCI">3.1 Interaction Design</option>
                                <option value="HCI_SCREEN">3.2 Screen Design</option>
                                <option value="Algorithm">Other: Algorithms</option>
                            </datalist>
                        </div>

                        <div>
                            <label for="content" class="block font-medium text-lg text-[#3E3E3E]">Lesson Content (Context) <span class="text-red-600">*</span></label>
                            <textarea name="content" id="content" rows="6" required
                                      class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50"><?php echo htmlspecialchars($lesson['content'] ?? ''); ?></textarea>
                        </div>
                        
                        <div>
                            <label for="duration" class="block font-medium text-lg text-[#3E3E3E]">Estimated Duration (Mins)</label>
                            <input type="number" name="duration" id="duration" min="5" value="<?php echo htmlspecialchars($lesson['duration'] ?? ''); ?>"
                                   class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50">
                        </div>

                        <div>
                            <label for="url" class="block font-medium text-lg text-[#3E3E3E]">Lesson URL (Optional)</label>
                            <input type="url" name="url" id="url" value="<?php echo htmlspecialchars($lesson['url'] ?? ''); ?>"
                                   class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3 focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50">
                        </div>

                        <div>
                            <label for="material_file" class="block font-medium text-lg text-[#3E3E3E]">Lesson Material (PDF/File)</label>
                            <?php if ($lesson['material_path']): ?>
                                <p class="text-sm text-gray-600 mb-2">Current file: <a href="../../<?php echo htmlspecialchars($lesson['material_path']); ?>" target="_blank" class="text-blue-600 hover:underline"><?php echo basename($lesson['material_path']); ?></a></p>
                            <?php endif; ?>
                            <input type="file" name="material_file" id="material_file" accept=".pdf,.doc,.docx"
                                   class="mt-1 block w-full p-3 border border-gray-400 rounded-md bg-gray-50 cursor-pointer">
                            <p class="text-sm text-gray-500 mt-1">Leave empty to keep current file, or upload a new one to replace it.</p>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_published" id="is_published" <?php echo $lesson['is_published'] ? 'checked' : ''; ?> class="rounded border-gray-300 text-[#2454FF] focus:ring-[#2454FF]">
                                <span class="ml-2 text-sm text-gray-700">Publish this lesson</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-start space-x-4 pt-4">
                            <button type="submit" class="bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150">
                                Update Lesson
                            </button>
                            <a href="manage-lessons.php" class="text-gray-600 hover:text-gray-900 font-medium">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="JS/edit-lesson.js"></script>
    <script>
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

