<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - CompuPlay</title>
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

        /* Header Section */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1rem;
            color: #6b7280;
        }

        /* Notifications Card */
        .notifications-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .notifications-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 1.5rem;
            font-weight: 600;
            font-size: 1.125rem;
        }

        .notification-item {
            padding: 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: background-color 0.2s;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: #f9fafb;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon {
            width: 48px;
            height: 48px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
        }

        .notification-message {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .notification-meta {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        .notification-action {
            color: #3b82f6;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .no-notifications {
            padding: 3rem;
            text-align: center;
            color: #6b7280;
        }
        /* Language toggle */
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
            <a href="/dashboard?lang={{ $lang }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none;">
                <div class="logo">C</div>
                <div class="logo-text">CompuPlay</div>
            </a>
        </div>
        <div class="nav-links">
            <a href="/dashboard?lang={{ $lang }}" class="nav-link">{{ $lang === 'en' ? 'Dashboard' : 'Papan Pemuka' }}</a>
            <a href="/performance?lang={{ $lang }}" class="nav-link">{{ $lang === 'en' ? 'Track Student' : 'Lihat Prestasi' }}</a>
            <a href="/progress?lang={{ $lang }}" class="nav-link">{{ $lang === 'en' ? 'View Progress' : 'Lihat Perkembangan' }}</a>
            <a href="/manage-activities?lang={{ $lang }}" class="nav-link">{{ $lang === 'en' ? 'Manage Activities' : 'Mengendalikan Aktiviti' }}</a>
            <a href="/notifications?lang={{ $lang }}" class="nav-link active notification-badge">
                <span style="font-size: 1.25rem;">🔔</span>
                <span class="badge-dot"></span>
            </a>
        </div>
        <div class="user-dropdown">
            <span>test</span>
            <span>▼</span>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">{{ $lang === 'en' ? 'Performance Alerts' : 'Amaran Prestasi' }}</h1>
            <p class="page-subtitle">{{ $lang === 'en' ? 'Notifications for students scoring 20% and below' : 'Notifikasi untuk pelajar yang mendapat markah 20% dan ke bawah' }}</p>
        </div>

        <!-- Notifications Card -->
        <div class="notifications-card">
            <div class="notifications-header">
                🚩 {{ $lang === 'en' ? 'Low Performance Alerts' : 'Amaran Prestasi Rendah' }}
            </div>
            
            @if(count($notifications) > 0)
                @foreach($notifications as $notification)
                    <a href="{{ route('progress.index', ['class' => $notification['class']]) }}" 
                       style="text-decoration: none; color: inherit;">
                        <div class="notification-item">
                            <div class="notification-icon">🚩</div>
                            <div class="notification-content">
                                <div class="notification-title">
                                    {{ $notification['student']->name }} - Class {{ $notification['class'] }}
                                </div>
                                <div class="notification-message">
                                    Scored {{ $notification['score'] }}/3 ({{ $notification['percentage'] }}%) 
                                    in {{ $notification['lesson']->q1 ?? 'Lesson ' . $notification['lesson']->id }}
                                </div>
                                <div class="notification-meta">
                                    {{ $notification['created_at']->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            <div class="notification-action">
                                {{ $lang === 'en' ? 'View Progress' : 'Lihat Perkembangan' }} →
                            </div>
                        </div>
                    </a>
                @endforeach
            @else
                <div class="no-notifications">
                    <p>🎉 {{ $lang === 'en' ? 'No low performance alerts at this time.' : 'Tiada amaran prestasi rendah pada masa ini.' }}</p>
                    <p style="margin-top: 0.5rem; font-size: 0.875rem;">{{ $lang === 'en' ? 'All students are performing above 20%.' : 'Semua pelajar menunjukkan prestasi melebihi 20%.' }}</p>
                </div>
            @endif
        </div>
    </div>
    </div>
    <!-- Language Toggle -->
    <a href="?lang={{ $lang === 'en' ? 'ms' : 'en' }}" class="lang-toggle" title="{{ $lang === 'en' ? 'Switch to Malay' : 'Tukar ke Bahasa Inggeris' }}">
        {{ $lang === 'en' ? 'BM' : 'EN' }}
    </a>
</body>
</html>

