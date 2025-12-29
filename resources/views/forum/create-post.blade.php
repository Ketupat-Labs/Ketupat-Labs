<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cipta Post - Forum Material</title>
    <link rel="stylesheet" href="{{ asset('Forum/CSS/forum.css') }}">
    <link rel="stylesheet" href="{{ asset('Forum/CSS/create-post.css') }}">
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
                            <img src="{{ asset('assets/images/LOGOCompuPlay.png') }}" alt="Logo" class="h-10 w-auto">
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Papan Pemuka
                        </a>
                        <a href="{{ route('forum.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
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
                            <a href="#" onclick="event.preventDefault(); const userId = sessionStorage.getItem('userId'); if (userId) { window.location.href = '/profile/' + userId; } else { window.location.href = '/profile'; } return false;" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                            <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Tetapan</a>
                            <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Keluar</a>
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
                <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                    Papan Pemuka
                </a>
                <a href="{{ route('forum.index') }}" class="block pl-3 pr-4 py-2 border-l-4 border-blue-500 text-base font-medium text-blue-700 bg-blue-50 focus:outline-none focus:text-blue-800 focus:bg-blue-100 focus:border-blue-700 transition duration-150 ease-in-out">
                    Forum
                </a>
                <a href="#" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                    Bilik Darjah
                </a>
            </div>
        </div>
    </nav>

    <div class="create-post-header">
        <div class="header-left">
            <button class="back-btn" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </button>
        </div>
    </div>

    <div class="create-post-container">
        <div class="create-post-card">
            <div class="card-header">
                <h2>Cipta Post Baharu</h2>
                <p>Kongsi pendapat anda, tanya soalan, atau mulakan perbincangan</p>
            </div>

            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i> Post berjaya dicipta!
            </div>

            <div class="error-message" id="errorMessage"></div>

            <form id="createPostForm" autocomplete="off">
                <div class="form-section">
                    <div class="form-section-title">Pilih Forum</div>
                    <div class="form-group">
                        <label for="forumSelect">
                            Forum <span class="required">*</span>
                        </label>
                        <select id="forumSelect" name="forumSelect" class="form-control" required autocomplete="off">
                            <option value="">Pilih forum...</option>
                        </select>
                        <small>Pilih forum di mana anda ingin mencipta post ini</small>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Jenis Post</div>
                    <div class="category-grid">
                        <label class="category-option" id="postTypePost">
                            <input type="radio" name="postType" value="post" checked>
                            <span>Post Teks</span>
                        </label>
                        <label class="category-option" id="postTypeLink">
                            <input type="radio" name="postType" value="link">
                            <span>Pautan</span>
                        </label>
                        <label class="category-option" id="postTypePoll">
                            <input type="radio" name="postType" value="poll">
                            <span>Undian</span>
                        </label>
                    </div>
                </div>

                <div class="form-section" id="postDetailsSection">
                    <div class="form-section-title">Butiran Post</div>
                    
                    <div class="form-group">
                        <label for="postTitle">
                            Tajuk Post <span class="required">*</span>
                        </label>
                        <input type="text" id="postTitle" name="postTitle" class="form-control" placeholder="Masukkan tajuk yang jelas dan deskriptif" required autocomplete="off">
                        <small>Jelas tentang apa yang anda tanya atau bincangkan</small>
                    </div>

                    <div class="form-group" id="contentGroup">
                        <label for="postContent">
                            Kandungan <span class="required">*</span>
                        </label>
                        <textarea id="postContent" name="postContent" class="form-control" rows="8" placeholder="Tulis kandungan post anda di sini..." required minlength="10" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" style="resize: vertical; min-height: 120px; max-height: 500px; height: 120px; overflow-y: auto;"></textarea>
                        <small>Minimum 10 aksara. Jelas dan terperinci dalam penjelasan anda.</small>
                    </div>

                    <div class="form-group" id="linkGroup" style="display: none;">
                        <label for="postLink">
                            Pautan URL <span class="required">*</span>
                        </label>
                        <input type="url" id="postLink" name="postLink" class="form-control" placeholder="https://example.com" autocomplete="off">
                        <small>Masukkan URL penuh yang anda ingin kongsi</small>
                    </div>

                    <div class="form-group" id="pollGroup" style="display: none;">
                        <label>
                            Pilihan Undian <span class="required">*</span>
                        </label>
                        <div id="pollOptionsContainer"></div>
                        <button type="button" id="addPollOption" class="btn-secondary">
                            <i class="fas fa-plus"></i> Tambah Pilihan
                        </button>
                        <small>Tambah sekurang-kurangnya 2 pilihan untuk undian anda</small>
                    </div>
                </div>

                <!-- Attachment Section for Text Posts -->
                <div class="form-section" id="attachmentSectionPost">
                    <div class="form-section-title">Lampiran (Pilihan)</div>
                    
                    <div class="form-group">
                        <input type="file" id="attachmentInputPost" multiple accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,.doc,.docx">
                        <div id="attachmentsPreviewPost" class="attachments-preview"></div>
                        <small>Muat naik imej atau fail. Saiz fail maksimum: 50MB setiap satu.</small>
                    </div>
                </div>

                <!-- Attachment Section for Link Posts -->
                <div class="form-section" id="attachmentSectionLink" style="display: none;">
                    <div class="form-section-title">Lampiran (Pilihan)</div>
                    
                    <div class="form-group">
                        <input type="file" id="attachmentInputLink" multiple accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,.doc,.docx">
                        <div id="attachmentsPreviewLink" class="attachments-preview"></div>
                        <small>Muat naik imej atau fail. Saiz fail maksimum: 50MB setiap satu.</small>
                    </div>
                </div>

                <!-- Lesson Attachment Section -->
                @if(isset($lessons) && count($lessons) > 0)
                <div class="form-section" id="lessonSection">
                    <div class="form-section-title">Kongsi Pelajaran (Pilihan)</div>
                    <div class="form-group">
                        <label for="lessonSelect">Pilih Pelajaran</label>
                        <select id="lessonSelect" name="lesson_id" class="form-control">
                            <option value="">-- Tiada Pelajaran Dipilih --</option>
                            @foreach($lessons as $lesson)
                                <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                            @endforeach
                        </select>
                        <small>Pilih pelajaran untuk dikongsi dengan guru lain.</small>
                    </div>
                </div>
                @endif

                <div class="form-section">
                    <div class="form-section-title">Tag (Pilihan)</div>
                    
                    <div class="form-group">
                        <label for="tagsInput">Tag</label>
                        <input type="text" id="tagsInput" name="tagsInput" class="form-control" placeholder="Tambah tag (tekan Enter)" autocomplete="off">
                        <div class="tag-input-container" id="tagsContainer"></div>
                        <small>Tekan Enter untuk menambah tag. Klik X untuk membuang.</small>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn-cancel" onclick="window.history.back()">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i> Cipta Post
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('Forum/JS/create-post.js') }}"></script>
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


