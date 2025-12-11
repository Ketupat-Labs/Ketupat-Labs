<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Create Post - Material Forum</title>
    <link rel="stylesheet" href="<?php echo e(asset('Forum/CSS/forum.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('Forum/CSS/create-post.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
                        <a href="../Dashboard/dashboard.php" class="flex items-center space-x-3">
                            <img src="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>" alt="Logo" class="h-10 w-auto">
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Dashboard
                        </a>
                        <a href="<?php echo e(route('forum.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
                            Forum
                        </a>
                        <a href="#" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Classroom
                        </a>
                    </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <div class="relative">
                        <button id="userMenuBtn" class="inline-flex items-center px-4 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-lg text-gray-800 bg-white hover:bg-blue-50 hover:border-blue-300 focus:outline-none transition ease-in-out duration-150">
                            <div id="userName">User</div>
                            <svg class="fill-current h-4 w-4 ms-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="<?php echo e(route('logout')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Out</a>
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
                <a href="<?php echo e(route('dashboard')); ?>" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                    Dashboard
                </a>
                <a href="<?php echo e(route('forum.index')); ?>" class="block pl-3 pr-4 py-2 border-l-4 border-blue-500 text-base font-medium text-blue-700 bg-blue-50 focus:outline-none focus:text-blue-800 focus:bg-blue-100 focus:border-blue-700 transition duration-150 ease-in-out">
                    Forum
                </a>
                <a href="#" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                    Classroom
                </a>
            </div>
        </div>
    </nav>

    <div class="create-post-header">
        <div class="header-left">
            <button class="back-btn" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i>
                Back
            </button>
        </div>
    </div>

    <div class="create-post-container">
        <div class="create-post-card">
            <div class="card-header">
                <h2>Create New Post</h2>
                <p>Share your thoughts, ask questions, or start a discussion</p>
            </div>

            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i> Post created successfully!
            </div>

            <div class="error-message" id="errorMessage"></div>

            <form id="createPostForm" autocomplete="off">
                <div class="form-section">
                    <div class="form-section-title">Select Forum</div>
                    <div class="form-group">
                        <label for="forumSelect">
                            Forum <span class="required">*</span>
                        </label>
                        <select id="forumSelect" name="forumSelect" class="form-control" required autocomplete="off">
                            <option value="">Choose a forum...</option>
                        </select>
                        <small>Select the forum where you want to create this post</small>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Post Type</div>
                    <div class="category-grid">
                        <label class="category-option" id="postTypePost">
                            <input type="radio" name="postType" value="post" checked>
                            <span>Text Post</span>
                        </label>
                        <label class="category-option" id="postTypeLink">
                            <input type="radio" name="postType" value="link">
                            <span>Link</span>
                        </label>
                        <label class="category-option" id="postTypePoll">
                            <input type="radio" name="postType" value="poll">
                            <span>Poll</span>
                        </label>
                    </div>
                </div>

                <div class="form-section" id="postDetailsSection">
                    <div class="form-section-title">Post Details</div>
                    
                    <div class="form-group">
                        <label for="postTitle">
                            Post Title <span class="required">*</span>
                        </label>
                        <input type="text" id="postTitle" name="postTitle" class="form-control" placeholder="Enter a clear and descriptive title" required autocomplete="off">
                        <small>Be specific about what you're asking or discussing</small>
                    </div>

                    <div class="form-group" id="contentGroup">
                        <label for="postContent">
                            Content <span class="required">*</span>
                        </label>
                        <textarea id="postContent" name="postContent" class="form-control" rows="8" placeholder="Write your post content here..." required minlength="10" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" style="resize: vertical; min-height: 120px; max-height: 500px; height: 120px; overflow-y: auto;"></textarea>
                        <small>Minimum 10 characters. Be clear and detailed in your explanation.</small>
                    </div>

                    <div class="form-group" id="linkGroup" style="display: none;">
                        <label for="postLink">
                            URL Link <span class="required">*</span>
                        </label>
                        <input type="url" id="postLink" name="postLink" class="form-control" placeholder="https://example.com" autocomplete="off">
                        <small>Enter the full URL you want to share</small>
                    </div>

                    <div class="form-group" id="pollGroup" style="display: none;">
                        <label>
                            Poll Options <span class="required">*</span>
                        </label>
                        <div id="pollOptionsContainer"></div>
                        <button type="button" id="addPollOption" class="btn-secondary">
                            <i class="fas fa-plus"></i> Add Option
                        </button>
                        <small>Add at least 2 options for your poll</small>
                    </div>
                </div>

                <div class="form-section" id="attachmentSection">
                    <div class="form-section-title">Attachments (Optional)</div>
                    
                    <div class="form-group">
                        <input type="file" id="attachmentInput" multiple accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,.doc,.docx">
                        <div id="attachmentsPreview" class="attachments-preview"></div>
                        <small>Upload images or files. Maximum file size: 50MB each.</small>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Tags (Optional)</div>
                    
                    <div class="form-group">
                        <label for="tagsInput">Tags</label>
                        <input type="text" id="tagsInput" name="tagsInput" class="form-control" placeholder="Add tags (press Enter)" autocomplete="off">
                        <div class="tag-input-container" id="tagsContainer"></div>
                        <small>Press Enter to add a tag. Click the X to remove.</small>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn-cancel" onclick="window.history.back()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i> Create Post
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?php echo e(asset('Forum/JS/create-post.js')); ?>"></script>
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


<?php /**PATH C:\xampp\htdocs\Material\resources\views/forum/create-post.blade.php ENDPATH**/ ?>