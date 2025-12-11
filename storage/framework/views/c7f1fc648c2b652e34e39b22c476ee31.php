<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Cipta Forum - Forum Material</title>
    <link rel="stylesheet" href="<?php echo e(asset('Forum/CSS/forum.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('Forum/CSS/create-forum.css')); ?>">
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
                        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center space-x-3">
                            <img src="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>" alt="Logo" class="h-10 w-auto">
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Papan Pemuka
                        </a>
                        <a href="<?php echo e(route('forum.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
                            Forum
                        </a>
                        <a href="#" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Bilik Darjah
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
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                            <a href="<?php echo e(route('logout')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Keluar</a>
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
                    Papan Pemuka
                </a>
                <a href="<?php echo e(route('forum.index')); ?>" class="block pl-3 pr-4 py-2 border-l-4 border-blue-500 text-base font-medium text-blue-700 bg-blue-50 focus:outline-none focus:text-blue-800 focus:bg-blue-100 focus:border-blue-700 transition duration-150 ease-in-out">
                    Forum
                </a>
                <a href="#" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                    Bilik Darjah
                </a>
            </div>

            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800" id="mobileUserName">User</div>
                    <div class="font-medium text-sm text-gray-500" id="mobileUserEmail"></div>
                </div>

                <div class="mt-3 space-y-1">
                    <a href="#" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                        Profil
                    </a>
                    <a href="<?php echo e(route('logout')); ?>" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">
                        Log Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="create-forum-header" style="margin-top: 0; padding: 16px 32px;">
        <div class="header-left">
            <a href="<?php echo e(route('forum.index')); ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Forum
            </a>
        </div>
    </div>

    <div class="create-forum-container">
        <div class="create-forum-card">
            <div class="card-header">
                <h2>Cipta Forum Baharu</h2>
                <p>Mulakan komuniti baharu untuk perbincangan dan kerjasama</p>
            </div>

            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i> Forum berjaya dicipta!
            </div>

            <div class="error-message" id="errorMessage"></div>

            <form id="createForumForm">
                <div class="form-section">
                    <div class="form-section-title">Maklumat Asas</div>
                    
                    <div class="form-group">
                        <label for="forumTitle">
                            Tajuk Forum <span class="required">*</span>
                        </label>
                        <input type="text" id="forumTitle" placeholder="cth., Pengenalan kepada HCI" required>
                        <small>Pilih tajuk yang jelas dan deskriptif</small>
                    </div>

                    <div class="form-group">
                        <label for="forumDescription">
                            Penerangan <span class="required">*</span>
                        </label>
                        <textarea id="forumDescription" rows="4" placeholder="Terangkan tentang forum ini..." required minlength="20"></textarea>
                        <small>Minimum 20 aksara. Jelas dan terperinci.</small>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Privasi & Keterlihatan</div>
                    
                    <div class="form-group">
                        <label for="forumVisibility">
                            Keterlihatan
                        </label>
                        <select id="forumVisibility" onchange="handleVisibilityChange()">
                            <option value="public">Awam - Sesiapa boleh lihat dan post</option>
                            <option value="class">Kelas Sahaja - Hanya ahli kelas</option>
                            <option value="specific">Ahli Tertentu - Jemput sahaja</option>
                        </select>
                        <small>Kawal siapa yang boleh akses forum ini</small>
                    </div>

                    <div class="form-group" id="classSelectionGroup" style="display: none;">
                        <label for="selectedClass">
                            Pilih Bilik Darjah <span class="required">*</span>
                        </label>
                        <select id="selectedClass">
                            <option value="">Pilih bilik darjah...</option>
                        </select>
                        <small>Forum ini hanya akan kelihatan kepada ahli kelas yang dipilih</small>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Jadual (Pilihan)</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="forumStartDate">Tarikh Mula</label>
                            <input type="datetime-local" id="forumStartDate" style="height: 44px; max-height: 44px; min-height: 44px;" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <small>Bilakah forum ini harus menjadi aktif?</small>
                        </div>

                        <div class="form-group">
                            <label for="forumEndDate">Tarikh Tamat</label>
                            <input type="datetime-local" id="forumEndDate" style="height: 44px; max-height: 44px; min-height: 44px;" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <small>Bilakah forum ini harus ditutup?</small>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="<?php echo e(route('forum.index')); ?>" class="btn-cancel">
                        Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i> Cipta Forum
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?php echo e(asset('Forum/JS/create-forum.js')); ?>"></script>
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


<?php /**PATH C:\xampp\htdocs\Material\resources\views/forum/create-forum.blade.php ENDPATH**/ ?>