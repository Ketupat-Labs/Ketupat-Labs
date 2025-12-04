<!DOCTYPE html>
<html lang="{{ request('lang', 'en') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CompuPlay Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f7fa;
            color: #1a1a1a;
            line-height: 1.6;
        }

        /* Navigation Bar */
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
            color: white;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: #6b7280;
            font-weight: 500;
            transition: color 0.2s;
            padding: 0.5rem 0;
        }

        .nav-link.active {
            color: #3b82f6;
            border-bottom: 2px solid #3b82f6;
        }

        .nav-link:hover {
            color: #3b82f6;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: #1a1a1a;
            font-weight: 500;
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Welcome Section */
        .welcome-section {
            margin-bottom: 2rem;
        }

        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            font-size: 1rem;
            color: #6b7280;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.blue {
            background-color: #dbeafe;
            color: #3b82f6;
        }

        .stat-icon.green {
            background-color: #d1fae5;
            color: #10b981;
        }

        .stat-icon.orange {
            background-color: #fed7aa;
            color: #f97316;
        }

        .stat-icon.yellow {
            background-color: #fef3c7;
            color: #f59e0b;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        /* Quick Access Section */
        .quick-access {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .quick-card {
            border-radius: 16px;
            padding: 2rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        .quick-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .quick-card.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .quick-card.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .quick-card.orange {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .quick-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .quick-card-icon {
            font-size: 32px;
        }

        .quick-card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .quick-card-desc {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .quick-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .quick-card-status {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .quick-card-arrow {
            font-size: 20px;
        }

        /* Bottom Sections */
        .bottom-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .section-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .section-link {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .section-link:hover {
            text-decoration: underline;
        }

        /* Recent Lessons */
        .lesson-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-bottom: 0.5rem;
        }

        .lesson-item:hover {
            background-color: #f9fafb;
        }

        .lesson-icon {
            width: 40px;
            height: 40px;
            background-color: #dbeafe;
            color: #3b82f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .lesson-content {
            flex: 1;
        }

        .lesson-title {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
        }

        .lesson-meta {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .lesson-arrow {
            color: #9ca3af;
            font-size: 18px;
        }

        /* Quick Actions */
        .action-button {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-bottom: 0.5rem;
            text-decoration: none;
            color: inherit;
        }

        .action-button:hover {
            background-color: #f9fafb;
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .action-icon.green {
            background-color: #d1fae5;
            color: #10b981;
        }

        .action-icon.orange {
            background-color: #fed7aa;
            color: #f97316;
        }

        .action-icon.yellow {
            background-color: #fef3c7;
            color: #f59e0b;
        }

        .action-text {
            flex: 1;
            font-weight: 500;
            color: #1a1a1a;
        }

        .action-arrow {
            color: #9ca3af;
            font-size: 18px;
        }

        .lang-toggle {
            position: fixed;
            right: 1.5rem;
            bottom: 1.5rem;
            width: 50px;
            height: 50px;
            border-radius: 9999px;
            background: #111827;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 10px 15px rgba(0,0,0,0.25);
            cursor: pointer;
            z-index: 50;
            transition: transform 0.2s;
        }

        .lang-toggle:hover {
            background: #1f2937;
            transform: scale(1.05);
        }
        
        .notification-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        
        .badge-dot {
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }
    </style>
</head>
<body>
    @php $lang = request('lang', 'en'); @endphp
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo-container">
            <div class="logo">C</div>
            <div class="logo-text">CompuPlay</div>
        </div>
        <div class="nav-links">
            <a href="{{ url('/dashboard') }}?lang={{ $lang }}" class="nav-link active">{{ $lang === 'en' ? 'Dashboard' : 'Papan Pemuka' }}</a>
            <a href="{{ url('/performance') }}?lang={{ $lang }}" class="nav-link">{{ $lang === 'en' ? 'Track Student' : 'Lihat Prestasi' }}</a>
            <a href="{{ url('/progress') }}?lang={{ $lang }}" class="nav-link">{{ $lang === 'en' ? 'View Progress' : 'Lihat Perkembangan' }}</a>
            <a href="{{ url('/manage-activities') }}?lang={{ $lang }}" class="nav-link">{{ $lang === 'en' ? 'Manage Activities' : 'Mengendalikan Aktiviti' }}</a>
            <a href="{{ url('/notifications') }}?lang={{ $lang }}" class="nav-link notification-badge">
                <span style="font-size: 1.25rem;">🔔</span>
                <!-- We could add a red dot here if there are unread notifications -->
                <span class="badge-dot"></span>
            </a>
        </div>
        <div class="user-dropdown">
            <span>{{ $user->name ?? 'test' }}</span>
            <span>▼</span>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">{{ $lang === 'en' ? 'Welcome back' : 'Selamat kembali' }}, {{ $user->name ?? 'test' }}!</h1>
            <p class="welcome-subtitle">{{ $lang === 'en' ? 'Continue your learning journey.' : 'Teruskan perjalanan pembelajaran anda.' }}</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📚</div>
                <div class="stat-content">
                    <div class="stat-label">{{ $lang === 'en' ? 'Published Lessons' : 'Pelajaran Diterbitkan' }}</div>
                    <div class="stat-value">{{ $publishedLessons }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">📄</div>
                <div class="stat-content">
                    <div class="stat-label">{{ $lang === 'en' ? 'Your Lessons' : 'Pelajaran Anda' }}</div>
                    <div class="stat-value">{{ $userLessons }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">📋</div>
                <div class="stat-content">
                    <div class="stat-label">{{ $lang === 'en' ? 'Quiz Attempts' : 'Percubaan Kuiz' }}</div>
                    <div class="stat-value">{{ $quizAttempts }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow">☁️</div>
                <div class="stat-content">
                    <div class="stat-label">{{ $lang === 'en' ? 'Submissions' : 'Serahan' }}</div>
                    <div class="stat-value">{{ $submissions }}</div>
                </div>
            </div>
        </div>

        <!-- Quick Access Section -->
        <div class="quick-access">
            <a href="{{ url('/performance') }}?lang={{ $lang }}" class="quick-card blue">
                <div>
                    <div class="quick-card-header">
                        <div class="quick-card-icon">📊</div>
                    </div>
                    <div class="quick-card-title">{{ $lang === 'en' ? 'Track Student' : 'Lihat Prestasi' }}</div>
                    <div class="quick-card-desc">{{ $lang === 'en' ? 'Monitor and analyze student performance' : 'Pantau dan analisis prestasi pelajar' }}</div>
                </div>
                <div class="quick-card-footer">
                    <div class="quick-card-status">→ View performance data</div>
                    <div class="quick-card-arrow">→</div>
                </div>
            </a>
            <div class="quick-card green">
                <div>
                    <div class="quick-card-header">
                        <div class="quick-card-icon">📝</div>
                    </div>
                    <div class="quick-card-title">Manage Lessons</div>
                    <div class="quick-card-desc">Create and manage your lesson content</div>
                </div>
                <div class="quick-card-footer">
                    <div class="quick-card-status">→ {{ $userLessons }} lessons created</div>
                    <div class="quick-card-arrow">→</div>
                </div>
            </div>
            <div class="quick-card orange">
                <div>
                    <div class="quick-card-header">
                        <div class="quick-card-icon">☁️</div>
                    </div>
                    <div class="quick-card-title">Submit Assignment</div>
                    <div class="quick-card-desc">Upload your practical work</div>
                </div>
                <div class="quick-card-footer">
                    <div class="quick-card-status">→ 0 pending</div>
                    <div class="quick-card-arrow">→</div>
                </div>
            </div>
        </div>

        <!-- Bottom Sections -->
        <div class="bottom-sections">
            <!-- Recent Lessons -->
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">{{ $lang === 'en' ? 'Recent Lessons' : 'Pelajaran Terkini' }}</h2>
                    <a href="{{ url('/performance') }}?lang={{ $lang }}" class="section-link">{{ $lang === 'en' ? 'View all' : 'Lihat semua' }}</a>
                </div>
                @if($recentLessons->count() > 0)
                    @foreach($recentLessons->take(3) as $lesson)
                        <div class="lesson-item">
                            <div class="lesson-icon">📖</div>
                            <div class="lesson-content">
                                <div class="lesson-title">{{ $lesson->title ?? 'Introduction to Interaction Design' }}</div>
                                <div class="lesson-meta">{{ $lesson->category ?? 'HCI' }} • {{ $lesson->duration ?? '12 mins' }}</div>
                            </div>
                            <div class="lesson-arrow">→</div>
                        </div>
                    @endforeach
                @else
                    <div class="lesson-item">
                        <div class="lesson-icon">📖</div>
                        <div class="lesson-content">
                            <div class="lesson-title">Introduction to Interaction Design</div>
                            <div class="lesson-meta">HCI • 12 mins</div>
                        </div>
                        <div class="lesson-arrow">→</div>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">Quick Actions</h2>
                </div>
                <a href="/performance" class="action-button">
                    <div class="action-icon green">+</div>
                    <div class="action-text">Create New Lesson</div>
                    <div class="action-arrow">→</div>
                </a>
                <a href="/performance" class="action-button">
                    <div class="action-icon orange">📋</div>
                    <div class="action-text">Take a Quiz</div>
                    <div class="action-arrow">→</div>
                </a>
                <a href="/performance" class="action-button">
                    <div class="action-icon yellow">☁️</div>
                    <div class="action-text">Submit Assignment</div>
                    <div class="action-arrow">→</div>
                </a>
            </div>
        </div>
    </div>
    <!-- Language Toggle -->
    <a href="?lang={{ $lang === 'en' ? 'ms' : 'en' }}" class="lang-toggle" title="{{ $lang === 'en' ? 'Switch to Malay' : 'Tukar ke Bahasa Inggeris' }}">
        {{ $lang === 'en' ? 'BM' : 'EN' }}
    </a>
</body>
</html>

