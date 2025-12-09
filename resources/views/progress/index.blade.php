<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Progress - CompuPlay</title>
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

        /* Filters Card */
        .filters-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .filters {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
        }

        .filter-select {
            padding: 0.625rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            font-size: 0.875rem;
            color: #1a1a1a;
            cursor: pointer;
            transition: border-color 0.2s;
            min-width: 200px;
        }

        .filter-select:hover {
            border-color: #3b82f6;
        }

        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Progress Table Card */
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .table-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9fafb;
            color: #1a1a1a;
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e5e7eb;
        }

        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-not-started {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-in-progress {
            background: #fef3c7;
            color: #92400e;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-low-score {
            background: #fee2e2;
            color: #991b1b;
        }

        .percentage-bar {
            width: 100px;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            display: inline-block;
            margin-right: 0.5rem;
        }

        .percentage-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
            transition: width 0.3s;
        }

        .student-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .student-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .flag-icon {
            color: #ef4444;
            font-size: 1.25rem;
            margin-left: 0.5rem;
            cursor: help;
        }

        /* Summary Card */
        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 1.5rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .summary-item {
            padding: 1rem;
            background: #f9fafb;
            border-radius: 8px;
        }

        .summary-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3b82f6;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo-container">
            <a href="/dashboard" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none;">
                <div class="logo">C</div>
                <div class="logo-text">CompuPlay</div>
            </a>
        </div>
        <div class="nav-links">
            <a href="/dashboard" class="nav-link">Papan Pemuka</a>
            <a href="/performance" class="nav-link">Lihat Prestasi</a>
            <a href="/progress" class="nav-link active">Lihat Perkembangan</a>
            <a href="/manage-activities" class="nav-link">Mengendalikan Aktiviti</a>
            <a href="/notifications" class="nav-link notification-badge">
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
            <h1 class="page-title">Jejak Perkembangan Pelajaran</h1>
            <p class="page-subtitle">Pantau status penyelesaian pelajar merentasi semua pelajaran</p>
        </div>

        <!-- Filters Card -->
        <div class="filters-card">
            <form method="GET" action="{{ route('progress.index') }}" class="filters">
                <div class="filter-group">
                    <label class="filter-label">Kelas</label>
                    <select name="class" class="filter-select" onchange="this.form.submit()">
                        @foreach($classes as $class)
                            <option value="{{ $class }}" {{ $selectedClass == $class ? 'selected' : '' }}>
                                Kelas {{ $class }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Progress Table -->
        <div class="table-card">
            <div class="table-header">
                📊 Jadual Perkembangan Pelajaran - Kelas {{ $selectedClass }}
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Pelajar</th>
                            @foreach($lessons as $lesson)
                                <th>Pelajaran {{ $loop->iteration }}</th>
                            @endforeach
                            <th>Peratus Penyelesaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($progressData as $progress)
                            <tr>
                                <td>
                                    <a href="{{ route('performance.student', $progress['student']->student_id) }}" 
                                       class="student-link">
                                        👤 {{ $progress['student']->name }}
                                    </a>
                                    @php
                                        $hasLowScore = false;
                                        foreach($progress['lessons'] as $lessonProgress) {
                                            if($lessonProgress['status'] === 'Completed (Low Score)') {
                                                $hasLowScore = true;
                                                break;
                                            }
                                        }
                                    @endphp
                                    @if($hasLowScore)
                                        <span class="flag-icon" title="Below 20%">🚩</span>
                                    @endif
                                </td>
                                @foreach($progress['lessons'] as $lessonProgress)
                                    <td>
                                        @php
                                            $statusClass = 'status-not-started';
                                            if($lessonProgress['status'] === 'Completed') {
                                                $statusClass = 'status-completed';
                                            } elseif($lessonProgress['status'] === 'Completed (Low Score)') {
                                                $statusClass = 'status-low-score';
                                            } elseif($lessonProgress['status'] === 'In Progress') {
                                                $statusClass = 'status-in-progress';
                                            }
                                            
                                            $statusText = $lessonProgress['status'];
                                            if($statusText === 'Completed') $statusText = 'Selesai';
                                            elseif($statusText === 'Completed (Low Score)') $statusText = 'Selesai (Markah Rendah)';
                                            elseif($statusText === 'In Progress') $statusText = 'Sedang Berjalan';
                                            elseif($statusText === 'Not Started') $statusText = 'Belum Mula';
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                @endforeach
                                <td>
                                    <div class="percentage-bar">
                                        <div class="percentage-fill" style="width: {{ $progress['completionPercentage'] }}%"></div>
                                    </div>
                                    <strong>{{ $progress['completionPercentage'] }}%</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="summary-card">
            <h2 class="summary-title">Ringkasan Perkembangan</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Jumlah Pelajar</div>
                    <div class="summary-value">{{ $summary['totalStudents'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Jumlah Pelajaran</div>
                    <div class="summary-value">{{ $summary['totalLessons'] }}</div>
                </div>
                @foreach($summary['lessonCompletion'] as $lessonCompletion)
                    <div class="summary-item">
                        <div class="summary-label">{{ $lessonCompletion['lesson']->q1 ?? 'Lesson ' . $lessonCompletion['lesson']->id }}</div>
                        <div class="summary-value">{{ $lessonCompletion['completed'] }}/{{ $lessonCompletion['total'] }}</div>
                        <div class="summary-label">{{ $lessonCompletion['percentage'] }}% selesai</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    </div>
</body>
</html>

