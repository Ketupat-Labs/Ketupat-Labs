<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Activities - CompuPlay</title>
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

        /* Two Column Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Calendar Card */
        .calendar-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .calendar-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .calendar-title {
            font-weight: 600;
            font-size: 1.125rem;
        }

        .calendar-nav {
            display: flex;
            gap: 0.5rem;
        }

        .calendar-nav-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .calendar-nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .calendar-body {
            padding: 1rem;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .calendar-weekday {
            text-align: center;
            font-weight: 600;
            font-size: 0.75rem;
            color: #6b7280;
            padding: 0.5rem;
            text-transform: uppercase;
        }

        .calendar-week {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }

        .calendar-day {
            aspect-ratio: 1;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .calendar-day:hover {
            background: #f9fafb;
            border-color: #3b82f6;
        }

        .calendar-day.other-month {
            opacity: 0.3;
        }

        .calendar-day.today {
            background: #dbeafe;
            border-color: #3b82f6;
            font-weight: 700;
        }

        .day-number {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .day-assignments {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .assignment-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #3b82f6;
        }

        .assignment-dot.overdue {
            background: #ef4444;
        }

        /* Assignment Form Card */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.625rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn {
            padding: 0.625rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-small {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        /* Student Status Card */
        .status-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .status-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
        }

        .status-table th {
            background: #f9fafb;
            color: #1a1a1a;
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e5e7eb;
        }

        .status-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }

        .status-table tr:hover {
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

        .status-overdue {
            background: #fee2e2;
            color: #991b1b;
        }

        .student-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .student-link:hover {
            text-decoration: underline;
        }

        /* Assignments List */
        .assignments-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .assignment-item {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .assignment-item:last-child {
            border-bottom: none;
        }

        .assignment-info {
            flex: 1;
        }

        .assignment-title {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
        }

        .assignment-date {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .assignment-date.overdue {
            color: #ef4444;
            font-weight: 600;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
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
            <a href="/progress" class="nav-link">Lihat Perkembangan</a>
            <a href="/manage-activities" class="nav-link active">Mengendalikan Aktiviti</a>
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
            <h1 class="page-title">Mengendalikan Aktiviti</h1>
            <p class="page-subtitle">Tetapkan tarikh akhir untuk pelajaran dan pantau perkembangan pelajar</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Filters Card -->
        <div class="filters-card">
            <form method="GET" action="{{ route('manage-activities.index') }}" class="filters">
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

        <!-- Two Column Layout -->
        <div class="content-grid">
            <!-- Calendar -->
            <div class="calendar-card">
                <div class="calendar-header">
                    <div class="calendar-title">📅 {{ $calendarData['month'] }}</div>
                    <div class="calendar-nav">
                        <a href="{{ route('manage-activities.index', ['class' => $selectedClass, 'month' => $calendarData['prevMonth']]) }}" 
                           class="calendar-nav-btn">← Sebelum</a>
                        <a href="{{ route('manage-activities.index', ['class' => $selectedClass, 'month' => date('Y-m')]) }}" 
                           class="calendar-nav-btn">Hari Ini</a>
                        <a href="{{ route('manage-activities.index', ['class' => $selectedClass, 'month' => $calendarData['nextMonth']]) }}" 
                           class="calendar-nav-btn">Seterusnya →</a>
                    </div>
                </div>
                <div class="calendar-body">
                    <div class="calendar-weekdays">
                        <div class="calendar-weekday">Ahad</div>
                        <div class="calendar-weekday">Isnin</div>
                        <div class="calendar-weekday">Selasa</div>
                        <div class="calendar-weekday">Rabu</div>
                        <div class="calendar-weekday">Khamis</div>
                        <div class="calendar-weekday">Jumaat</div>
                        <div class="calendar-weekday">Sabtu</div>
                    </div>
                    @foreach($calendarData['weeks'] as $week)
                        <div class="calendar-week">
                            @foreach($week as $day)
                                <div class="calendar-day {{ !$day['isCurrentMonth'] ? 'other-month' : '' }} {{ $day['isToday'] ? 'today' : '' }}"
                                     onclick="selectDate('{{ $day['date']->format('Y-m-d') }}')">
                                    <div class="day-number">{{ $day['date']->format('j') }}</div>
                                    <div class="day-assignments">
                                        @foreach($day['assignments'] as $assignment)
                                            @php
                                                $dueDate = $assignment->due_date instanceof \Carbon\Carbon 
                                                    ? $assignment->due_date 
                                                    : \Carbon\Carbon::parse($assignment->due_date);
                                                $isOverdue = $dueDate < now();
                                            @endphp
                                            <div class="assignment-dot {{ $isOverdue ? 'overdue' : '' }}" 
                                                 title="{{ $assignment->lesson->q1 ?? 'Lesson' }}"></div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Assignment Form -->
            <div class="form-card">
                <h2 class="form-title">Tetapkan Tarikh Akhir</h2>
                <form action="{{ route('manage-activities.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="class" value="{{ $selectedClass }}">
                    
                    <div class="form-group">
                        <label class="form-label">Pilih Pelajaran</label>
                        <select name="lesson_id" class="form-select" required>
                            <option value="">Pilih satu pelajaran...</option>
                            @foreach($lessons as $lesson)
                                <option value="{{ $lesson->id }}">
                                    {{ $lesson->q1 ?? 'Lesson ' . $lesson->id }} ({{ $lesson->class }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tarikh Akhir</label>
                        <input type="date" name="due_date" class="form-input" id="due_date_input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nota (Pilihan)</label>
                        <textarea name="notes" class="form-textarea" placeholder="Tambah nota peringatan..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Tetapkan Tarikh Akhir</button>
                </form>

                <!-- Current Assignments -->
                @if($assignments->count() > 0)
                    <div style="margin-top: 2rem;">
                        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #1a1a1a;">Pelajaran Terkini</h3>
                        <div class="assignments-list">
                            @foreach($assignments as $assignment)
                                <div class="assignment-item">
                                    <div class="assignment-info">
                                        <div class="assignment-title">{{ $assignment->lesson->q1 ?? 'Lesson ' . $assignment->lesson->id }}</div>
                                        @php
                                            $dueDate = $assignment->due_date instanceof \Carbon\Carbon 
                                                ? $assignment->due_date 
                                                : \Carbon\Carbon::parse($assignment->due_date);
                                            $isOverdue = $dueDate < now();
                                        @endphp
                                        <div class="assignment-date {{ $isOverdue ? 'overdue' : '' }}">
                                            Tarikh Akhir: {{ $dueDate->format('d M Y') }}
                                            @if($isOverdue)
                                                (Lewat)
                                            @endif
                                        </div>
                                        @if($assignment->notes)
                                            <div style="font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem; font-style: italic;">
                                                📝 {{ $assignment->notes }}
                                            </div>
                                        @endif
                                    </div>
                                    <form action="{{ route('manage-activities.delete', $assignment->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.75rem;">Padam</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Student Lesson Status -->
        <div class="status-card">
            <div class="status-header">
                📊 Status Pelajaran Pelajar - Kelas {{ $selectedClass }}
            </div>
            <div style="overflow-x: auto;">
                <table class="status-table">
                    <thead>
                        <tr>
                            <th>Nama Pelajar</th>
                            @foreach($lessons as $lesson)
                                <th>{{ $lesson->q1 ?? 'Lesson ' . $loop->iteration }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentStatus as $status)
                            <tr>
                                <td>
                                    <a href="{{ route('performance.student', $status['student']->student_id) }}" 
                                       class="student-link">
                                        👤 {{ $status['student']->name }}
                                    </a>
                                </td>
                                @foreach($status['lessons'] as $lessonStatus)
                                    <td>
                                        @php
                                            $statusClass = 'status-not-started';
                                            if($lessonStatus['status'] === 'Completed') {
                                                $statusClass = 'status-completed';
                                            } elseif($lessonStatus['status'] === 'Completed (Low Score)') {
                                                $statusClass = 'status-low-score';
                                            } elseif($lessonStatus['status'] === 'In Progress') {
                                                $statusClass = 'status-in-progress';
                                            } elseif($lessonStatus['status'] === 'Overdue') {
                                                $statusClass = 'status-overdue';
                                            }
                                            
                                            $statusText = $lessonStatus['status'];
                                            if($statusText === 'Completed') $statusText = 'Selesai';
                                            elseif($statusText === 'Completed (Low Score)') $statusText = 'Selesai (Markah Rendah)';
                                            elseif($statusText === 'In Progress') $statusText = 'Sedang Berjalan';
                                            elseif($statusText === 'Not Started') $statusText = 'Belum Mula';
                                            elseif($statusText === 'Overdue') $statusText = 'Lewat';
                                        @endphp
                                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                            <div>
                                                <span class="status-badge {{ $statusClass }}">
                                                    {{ $statusText }}
                                                </span>
                                                @if(isset($lessonStatus['percentage']) && $lessonStatus['percentage'] > 0)
                                                    <span style="font-size: 0.75rem; color: #6b7280; margin-left: 0.5rem;">
                                                        ({{ $lessonStatus['percentage'] }}%)
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if(isset($lessonStatus['is_low_score']) && $lessonStatus['is_low_score'])
                                                <form action="{{ route('manage-activities.resend-lesson') }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="student_id" value="{{ $status['student']->student_id }}">
                                                    <input type="hidden" name="lesson_id" value="{{ $lessonStatus['lesson']->id }}">
                                                    <button type="submit" class="btn btn-warning btn-small" 
                                                            onclick="return confirm('Hantar semula pelajaran ini kepada {{ $status['student']->name }}? Mereka akan dapat mengambil semula.')">
                                                        🔄 Hantar Semula
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($lessonStatus['due_date'])
                                                <div style="font-size: 0.75rem; color: #6b7280;">
                                                    Akhir: {{ \Carbon\Carbon::parse($lessonStatus['due_date'])->format('d M') }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function selectDate(date) {
            document.getElementById('due_date_input').value = date;
        }
    </script>
</body>
</html>

