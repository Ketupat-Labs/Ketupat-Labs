<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CompuPlay - Platform Pembelajaran Pintar</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/landing.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <img src="<?php echo e(asset('assets/images/LogoCompuPlay.jpg')); ?>" alt="CompuPlay Logo" class="logo-img">
            </div>
            <ul class="nav-menu">
                <li><a href="#features">Ciri-ciri</a></li>
                <li><a href="#about">Tentang</a></li>
                <li><a href="<?php echo e(route('login')); ?>" class="btn-login-nav">Log Masuk</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <div class="hero-overlay"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Ubah Pengalaman Pembelajaran Anda
                    <span class="english-subtitle">Transform Your Learning Experience</span>
                </h1>
                <p class="hero-description">
                    Platform pendidikan komprehensif yang direka untuk guru dan pelajar bekerjasama, 
                    belajar, dan mencapai kecemerlangan akademik bersama.
                </p>
                <p class="hero-description-english">
                    A comprehensive educational platform designed for teachers and students to collaborate, 
                    learn, and achieve academic excellence together.
                </p>
                <div class="hero-buttons">
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">
                        <i class="fas fa-rocket"></i> Mula Sekarang
                    </a>
                    <a href="#features" class="btn btn-secondary">
                        <i class="fas fa-info-circle"></i> Ketahui Lebih Lanjut
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="<?php echo e(asset('assets/images/hero-education.svg')); ?>" alt="Education Platform">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Ciri-ciri Platform</h2>
                <p class="english-subtitle">Platform Features</p>
                <p>Semua yang anda perlukan untuk pengajaran dan pembelajaran yang berkesan</p>
            </div>
            <div class="features-grid">
                <!-- Feature 1: Class Management -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3>Pengurusan Kelas</h3>
                    <p class="feature-desc-en">Class Management</p>
                    <p>Cipta, edit, dan urus kelas dengan mudah. Tambah pelajar secara individu atau pukal, 
                    jejaki pendaftaran, dan susun bahan pengajaran anda dengan cekap.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Cipta dan urus pelbagai kelas</li>
                        <li><i class="fas fa-check"></i> Pendaftaran pelajar pukal</li>
                        <li><i class="fas fa-check"></i> Alat penyusunan kelas</li>
                    </ul>
                </div>

                <!-- Feature 2: Lesson Management -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Pengurusan Pelajaran</h3>
                    <p class="feature-desc-en">Lesson Management</p>
                    <p>Cipta pelajaran komprehensif dengan kandungan multimedia, jejaki kemajuan pelajar, 
                    dan tetapkan pelajaran wajib untuk memastikan liputan kurikulum.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Pelajaran multimedia yang kaya</li>
                        <li><i class="fas fa-check"></i> Jejaki kemajuan</li>
                        <li><i class="fas fa-check"></i> Tugasan wajib</li>
                    </ul>
                </div>

                <!-- Feature 3: Interactive Learning -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <h3>Pembelajaran Interaktif</h3>
                    <p class="feature-desc-en">Interactive Learning</p>
                    <p>Libatkan pelajar dengan kuiz bergamifikasi, penilaian interaktif, dan jejakan kemajuan 
                    masa nyata untuk mengekalkan fokus dan motivasi.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Kuiz bergamifikasi</li>
                        <li><i class="fas fa-check"></i> Kemajuan masa nyata</li>
                        <li><i class="fas fa-check"></i> Laluan pembelajaran adaptif</li>
                    </ul>
                </div>

                <!-- Feature 4: Discussion Forum -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Forum Perbincangan</h3>
                    <p class="feature-desc-en">Discussion Forum</p>
                    <p>Menggalakkan kerjasama melalui forum berasaskan topik, siaran, komen, dan reaksi. 
                    Guru boleh menyederhanakan dan mengatur perbincangan dengan berkesan.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Perbincangan berasaskan topik</li>
                        <li><i class="fas fa-check"></i> Reaksi & komen siaran</li>
                        <li><i class="fas fa-check"></i> Alat penyederhanaan guru</li>
                    </ul>
                </div>

                <!-- Feature 5: Messaging -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Mesej Masa Nyata</h3>
                    <p class="feature-desc-en">Real-time Messaging</p>
                    <p>Berkomunikasi serta-merta dengan pelajar dan rakan sekerja melalui mesej langsung, 
                    sembang kumpulan, dan keupayaan perkongsian fail.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Mesej langsung</li>
                        <li><i class="fas fa-check"></i> Sokongan sembang kumpulan</li>
                        <li><i class="fas fa-check"></i> Lampiran fail</li>
                    </ul>
                </div>

                <!-- Feature 6: AI-Powered Features -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3>Pembelajaran Berkuasa AI</h3>
                    <p class="feature-desc-en">AI-Powered Learning</p>
                    <p>Memanfaatkan AI untuk menjana nota, mencipta kuiz, dan mendapat jawapan segera untuk 
                    soalan tentang bahan kursus.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Penjanaan nota AI</li>
                        <li><i class="fas fa-check"></i> Penciptaan kuiz automatik</li>
                        <li><i class="fas fa-check"></i> Sokongan Q&A segera</li>
                    </ul>
                </div>

                <!-- Feature 7: Performance Tracking -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Analitik Prestasi</h3>
                    <p class="feature-desc-en">Performance Analytics</p>
                    <p>Jejaki prestasi pelajar, kenal pasti kawasan yang sukar, dan jana laporan terperinci 
                    untuk menyokong keputusan pengajaran berasaskan data.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Laporan prestasi terperinci</li>
                        <li><i class="fas fa-check"></i> Pemantauan kemajuan</li>
                        <li><i class="fas fa-check"></i> Amaran automatik</li>
                    </ul>
                </div>

                <!-- Feature 8: Badges & Achievements -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Lencana & Pencapaian</h3>
                    <p class="feature-desc-en">Badges & Achievements</p>
                    <p>Motivasikan pelajar dengan sistem lencana komprehensif, jejaki pencapaian, 
                    dan raikan pencapaian pembelajaran.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Lencana yang boleh diperolehi</li>
                        <li><i class="fas fa-check"></i> Jejaki pencapaian</li>
                        <li><i class="fas fa-check"></i> Pencapaian yang boleh dikongsi</li>
                    </ul>
                </div>

                <!-- Feature 9: Notifications -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Notifikasi Pintar</h3>
                    <p class="feature-desc-en">Smart Notifications</p>
                    <p>Kekal dikemas kini dengan notifikasi masa nyata untuk mesej, aktiviti forum, 
                    tugasan, dan pengumuman penting.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Amaran masa nyata</li>
                        <li><i class="fas fa-check"></i> Pilihan yang boleh disesuaikan</li>
                        <li><i class="fas fa-check"></i> Sejarah notifikasi</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Tentang Platform Kami</h2>
                    <p class="english-subtitle">About Our Platform</p>
                    <p>
                        CompuPlay ialah sistem pengurusan pendidikan komprehensif yang direka 
                        untuk merapatkan jurang antara guru dan pelajar. Platform kami menyediakan pengalaman 
                        yang lancar untuk mengurus kelas, menyampaikan pelajaran, memudahkan perbincangan, dan 
                        menjejaki kemajuan akademik.
                    </p>
                    <p>
                        <strong>CompuPlay memudahkan pelajar untuk mempelajari Sains Komputer</strong> dengan pendekatan 
                        interaktif dan gamifikasi yang menarik. Bagi guru, platform ini dilengkapi dengan fungsi AI 
                        bersepadu yang memudahkan proses pengajaran, menjana bahan pembelajaran, dan mencipta kuiz 
                        secara automatik untuk membantu guru mengajar pelajar mereka dengan lebih berkesan.
                    </p>
                    
                    <div class="about-stats">
                        <div class="stat-item">
                            <div class="stat-number">100%</div>
                            <div class="stat-label">Fokus Pengguna</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Tersedia</div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="<?php echo e(asset('assets/images/picture1.jpg')); ?>" alt="About Platform">
                </div>
            </div>
        </div>
    </section>

    <!-- Key Benefits Section -->
    <section class="benefits">
        <div class="container">
            <div class="section-header">
                <h2>Mengapa Pilih CompuPlay?</h2>
                <p class="english-subtitle">Why Choose CompuPlay?</p>
            </div>
            <div class="benefits-grid">
                <div class="benefit-item">
                    <i class="fas fa-users"></i>
                    <h3>Untuk Guru</h3>
                    <p class="benefit-desc-en">For Teachers</p>
                    <p>Permudahkan pengurusan kelas, jejaki kemajuan pelajar, jana kandungan dengan AI, 
                    dan libatkan pelajar melalui forum dan mesej.</p>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Untuk Pelajar</h3>
                    <p class="benefit-desc-en">For Students</p>
                    <p>Akses pelajaran pada bila-bila masa, sertai perbincangan, dapatkan bantuan AI segera, jejaki 
                    kemajuan anda, dan perolehi lencana untuk pencapaian.</p>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-sync-alt"></i>
                    <h3>Kerjasama Masa Nyata</h3>
                    <p class="benefit-desc-en">Real-time Collaboration</p>
                    <p>Mesej segera, perbincangan forum langsung, dan notifikasi masa nyata memastikan 
                    semua orang terhubung dan terlibat.</p>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Selamat & Boleh Dipercayai</h3>
                    <p class="benefit-desc-en">Secure & Reliable</p>
                    <p>Data anda dilindungi dengan pengesahan yang selamat, kawalan akses berasaskan peranan, 
                    dan sandaran berkala.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Bersedia untuk Mengubah Pengalaman Pembelajaran Anda?</h2>
                <p class="english-subtitle">Ready to Transform Your Learning Experience?</p>
                <p>Sertai ribuan guru dan pelajar yang sudah menggunakan CompuPlay</p>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-large">
                    <i class="fas fa-rocket"></i> Mula Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><img src="<?php echo e(asset('assets/images/LogoCompuPlay.jpg')); ?>" alt="CompuPlay Logo" class="footer-logo"></h3>
                    <p>Platform pendidikan komprehensif untuk pengajaran dan pembelajaran moden.</p>
                </div>
                <div class="footer-section">
                    <h4>Pautan Pantas</h4>
                    <ul>
                        <li><a href="#features">Ciri-ciri</a></li>
                        <li><a href="#about">Tentang</a></li>
                        <li><a href="<?php echo e(route('login')); ?>">Log Masuk</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Ciri-ciri</h4>
                    <ul>
                        <li><a href="#features">Pengurusan Kelas</a></li>
                        <li><a href="#features">Pelajaran</a></li>
                        <li><a href="#features">Forum</a></li>
                        <li><a href="#features">Mesej</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Hubungi</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> abcdefghijklmnopqrstuvwxyz@idkwhatemail.my</li>
                        <li><i class="fas fa-phone"></i> +60 12-345 6789</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 CompuPlay. Hak cipta terpelihara.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo e(asset('assets/js/landing.js')); ?>"></script>
</body>
</html>

<?php /**PATH /Users/raudhahmaszamanie/Downloads/Ketupat-Labs-fix-REALMAIN/resources/views/index.blade.php ENDPATH**/ ?>