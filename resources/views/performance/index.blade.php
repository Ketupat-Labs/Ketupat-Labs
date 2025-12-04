<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Student - CompuPlay</title>
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

        /* Current Filters Badge */
        .current-filters {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .current-filters strong {
            font-weight: 600;
        }

        /* Performance Table Card */
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

        .correct {
            color: #10b981;
            font-weight: 600;
        }

        .incorrect {
            color: #ef4444;
            font-weight: 600;
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .flag-icon {
            color: #ef4444;
            font-size: 1.25rem;
            margin-left: 0.5rem;
            cursor: help;
            position: relative;
        }

        .flag-icon::after {
            content: "Below 20%";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1a1a1a;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            margin-bottom: 0.5rem;
        }

        .flag-icon:hover::after {
            opacity: 1;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 0.25rem;
        }

        .stat-subtext {
            font-size: 0.875rem;
            color: #9ca3af;
        }

        /* Lesson Title Header */
        .lesson-title-header {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #92400e;
            font-size: 1rem;
            border-bottom: 1px solid #f3f4f6;
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
            <a href="/performance?lang={{ $lang }}" class="nav-link active">{{ $lang === 'en' ? 'Track Student' : 'Lihat Prestasi' }}</a>
            <a href="/progress?lang={{ $lang }}" class="nav-link">{{ $lang === 'en' ? 'View Progress' : 'Lihat Perkembangan' }}</a>
            <a href="/manage-activities?lang={{ $lang }}" class="nav-link">{{ $lang === 'en' ? 'Manage Activities' : 'Mengendalikan Aktiviti' }}</a>
            <a href="/notifications?lang={{ $lang }}" class="nav-link notification-badge">
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
            <h1 class="page-title">{{ $lang === 'en' ? 'Student Performance Tracking' : 'Jadual Prestasi Pelajar' }}</h1>
            <p class="page-subtitle">{{ $lang === 'en' ? 'Monitor and analyze student performance across lessons' : 'Pantau dan analisis prestasi pelajar merentasi pelajaran' }}</p>
        </div>

        <!-- Filters Card -->
        <div class="filters-card">
            <form method="GET" action="{{ route('performance.index') }}" class="filters">
                <div class="filter-group">
                    <label class="filter-label">Class</label>
                    <select name="class" class="filter-select" onchange="this.form.submit()">
                        @foreach($classes as $class)
                            <option value="{{ $class }}" {{ $selectedClass == $class ? 'selected' : '' }}>
                                Class {{ $class }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Lesson</label>
                    <select name="lesson" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Lessons</option>
                        @foreach($lessons as $lesson)
                            <option value="{{ $lesson->id }}" {{ $selectedLesson == $lesson->id ? 'selected' : '' }}>
                                {{ $lesson->q1 ?? 'Lesson ' . $lesson->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Current Selection Info -->
        @if($selectedClass)
        <div class="current-filters">
            <span>📍</span>
            <span>Viewing: <strong>Class {{ $selectedClass }}</strong></span>
            @if($selectedLesson)
                @php $selectedLessonObj = $lessons->firstWhere('id', $selectedLesson) @endphp
                <span>|</span>
                <span><strong>{{ $selectedLessonObj->q1 ?? 'Selected Lesson' }}</strong></span>
            @else
                <span>|</span>
                <span><strong>All Lessons</strong></span>
            @endif
        </div>
        @endif

        <!-- Performance Table -->
        <div class="table-card">
            @if($selectedLesson)
                @php $currentLesson = $lessons->firstWhere('id', $selectedLesson) @endphp
                <div class="lesson-title-header">
                    📚 {{ $currentLesson->q1 ?? 'Selected Lesson' }} - 3 Questions
                </div>
            @endif
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>{{ $lang === 'en' ? 'Student Name' : 'Nama Pelajar' }}</th>
                            <th>{{ $lang === 'en' ? 'Class' : 'Kelas' }}</th>
                            @if($selectedLesson)
                                <!-- Show individual questions for selected lesson -->
                                @for($i = 1; $i <= 3; $i++)
                                    <th>Q{{ $i }}</th>
                                @endfor
                                <th>{{ $lang === 'en' ? 'Total Score' : 'Jumlah Markah' }}</th>
                            @else
                                <!-- Show lessons when no specific lesson selected -->
                                @foreach($lessons as $lesson)
                                    <th>{{ $lesson->q1 ?? 'Lesson ' . $lesson->id }}</th>
                                @endforeach
                                <th>{{ $lang === 'en' ? 'Overall Avg' : 'Purata Keseluruhan' }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($performanceData) > 0)
                            @foreach($performanceData as $data)
                                <tr>
                                    <td>
                                        <a href="{{ route('performance.student', $data['student']->student_id) }}" 
                                           class="student-link">
                                            👤 {{ $data['student']->name }}
                                        </a>
                                        @php
                                            $hasLowScore = false;
                                            foreach($data['answers'] as $lessonAnswer) {
                                                if(isset($lessonAnswer['total_marks'])) {
                                                    $percentage = ($lessonAnswer['total_marks'] / 3) * 100;
                                                    if($percentage <= 20) {
                                                        $hasLowScore = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if($hasLowScore)
                                            <span class="flag-icon" title="Below 20%">🚩</span>
                                        @endif
                                    </td>
                                    <td>{{ $data['student']->class }}</td>
                                    
                                    @if($selectedLesson)
                                        <!-- Show individual question results -->
                                        @php $lessonData = $data['answers'][$selectedLesson] ?? null @endphp
                                        @if($lessonData)
                                            <td class="{{ ($lessonData['answers']['q1'] ?? false) ? 'correct' : 'incorrect' }}">
                                                {{ ($lessonData['answers']['q1'] ?? false) ? '✓' : '✗' }}
                                            </td>
                                            <td class="{{ ($lessonData['answers']['q2'] ?? false) ? 'correct' : 'incorrect' }}">
                                                {{ ($lessonData['answers']['q2'] ?? false) ? '✓' : '✗' }}
                                            </td>
                                            <td class="{{ ($lessonData['answers']['q3'] ?? false) ? 'correct' : 'incorrect' }}">
                                                {{ ($lessonData['answers']['q3'] ?? false) ? '✓' : '✗' }}
                                            </td>
                                            <td><strong>{{ $lessonData['total_marks'] ?? 0 }}/3</strong></td>
                                        @else
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td><strong>N/A</strong></td>
                                        @endif
                                    @else
                                        <!-- Show marks for each lesson -->
                                        @php 
                                            $totalMarks = 0;
                                            $lessonCount = 0;
                                        @endphp
                                        @foreach($lessons as $lesson)
                                            @php $lessonData = $data['answers'][$lesson->id] ?? null @endphp
                                            <td class="{{ $lessonData ? ($lessonData['total_marks'] > 0 ? 'correct' : 'incorrect') : '' }}">
                                                @if($lessonData)
                                                    {{ $lessonData['total_marks'] }}/3
                                                    @php 
                                                        $totalMarks += $lessonData['total_marks'];
                                                        $lessonCount++;
                                                    @endphp
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endforeach
                                        <td><strong>
                                            @if($lessonCount > 0)
                                                {{ number_format($totalMarks / $lessonCount, 1) }}
                                            @else
                                                -
                                            @endif
                                        </strong></td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="100" class="no-data">
                                    📝 No student data found for the selected filters.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lesson Statistics -->
        @if($selectedLesson && isset($lessonStats) && $lessonStats)
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Class Average</div>
                <div class="stat-value">{{ number_format($lessonStats->average_marks, 1) }}</div>
                <div class="stat-subtext">out of 3</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Highest Score</div>
                <div class="stat-value">{{ $lessonStats->max_marks }}</div>
                <div class="stat-subtext">out of 3</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Lowest Score</div>
                <div class="stat-value">{{ $lessonStats->min_marks }}</div>
                <div class="stat-subtext">out of 3</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Completion</div>
                <div class="stat-value">{{ $lessonStats->total_students }}/{{ count($students) }}</div>
                <div class="stat-subtext">students</div>
            </div>
        </div>
        @endif
    </div>
    </div>
    <!-- Language Toggle -->
    <a href="?lang={{ $lang === 'en' ? 'ms' : 'en' }}" class="lang-toggle" title="{{ $lang === 'en' ? 'Switch to Malay' : 'Tukar ke Bahasa Inggeris' }}">
        {{ $lang === 'en' ? 'BM' : 'EN' }}
    </a>
</body>
</html>
