<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $student->name }} - Individual Performance Report</title>
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

        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: color 0.2s;
        }

        .back-button:hover {
            color: #2563eb;
        }

        /* Header Section */
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
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

        .correct {
            color: #10b981;
            font-weight: 600;
        }

        .incorrect {
            color: #ef4444;
            font-weight: 600;
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

        /* Summary Statistics */
        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
        }

        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-subtext {
            font-size: 0.875rem;
            opacity: 0.8;
        }

        .comparison-section {
            background: #f9fafb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .comparison-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 1rem;
        }

        .comparison-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .comparison-item {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
        }

        .comparison-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .comparison-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .better {
            color: #10b981;
        }

        .worse {
            color: #ef4444;
        }

        .equal {
            color: #6b7280;
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
            <a href="/dashboard" class="nav-link">Dashboard</a>
            <a href="/performance" class="nav-link">Track Student</a>
            <a href="/progress" class="nav-link">View Progress</a>
            <a href="/notifications" class="nav-link">Notifications</a>
            <a href="/manage-activities" class="nav-link">Manage Activities</a>
        </div>
        <div class="user-dropdown">
            <span>test</span>
            <span>▼</span>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <a href="{{ route('performance.index') }}" class="back-button">
            ← Back to Performance Summary
        </a>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">{{ $student->name }} - Individual Performance Report</h1>
            <p class="page-subtitle">Class: {{ $student->class }} | Student ID: {{ $student->student_id }}</p>
        </div>

        <!-- Performance Table -->
        <div class="table-card">
            <div class="table-header">
                📊 Lesson Performance Details
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Lesson</th>
                            <th>Question 1</th>
                            <th>Question 2</th>
                            <th>Question 3</th>
                            <th>Total Marks</th>
                            <th>Class Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($student->answers as $answer)
                            @php
                                $percentage = ($answer->total_marks / 3) * 100;
                                $isLowScore = $percentage <= 20;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $answer->lesson->q1 ?? 'Lesson ' . $answer->lesson->id }}</strong>
                                    @if($isLowScore)
                                        <span class="flag-icon" title="Below 20%">🚩</span>
                                    @endif
                                </td>
                                <td class="{{ $answer->q1_answer ? 'correct' : 'incorrect' }}">
                                    {{ $answer->q1_answer ? '✓' : '✗' }}
                                </td>
                                <td class="{{ $answer->q2_answer ? 'correct' : 'incorrect' }}">
                                    {{ $answer->q2_answer ? '✓' : '✗' }}
                                </td>
                                <td class="{{ $answer->q3_answer ? 'correct' : 'incorrect' }}">
                                    {{ $answer->q3_answer ? '✓' : '✗' }}
                                </td>
                                <td>
                                    <strong>{{ $answer->total_marks }}/3</strong>
                                    @if($isLowScore)
                                        <span class="flag-icon" title="Below 20%">🚩</span>
                                    @endif
                                </td>
                                <td>{{ number_format($classAverages[$answer->lesson_id] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="summary-card">
            <h2 class="summary-title">Performance Summary</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Student Total Marks</div>
                    <div class="stat-value">{{ $student->answers->sum('total_marks') }}</div>
                    <div class="stat-subtext">across all lessons</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Student Average</div>
                    <div class="stat-value">{{ number_format($overallStudentAvg, 2) }}</div>
                    <div class="stat-subtext">marks per lesson</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Class Average</div>
                    <div class="stat-value">{{ number_format($overallClassAvg, 2) }}</div>
                    <div class="stat-subtext">marks per lesson</div>
                </div>
            </div>

            <div class="comparison-section">
                <h3 class="comparison-title">Performance Comparison</h3>
                <div class="comparison-grid">
                    <div class="comparison-item">
                        <div class="comparison-label">Student Average</div>
                        <div class="comparison-value">{{ number_format($overallStudentAvg, 2) }}</div>
                        <div class="stat-subtext">marks per lesson</div>
                    </div>
                    <div class="comparison-item">
                        <div class="comparison-label">Class Average</div>
                        <div class="comparison-value">{{ number_format($overallClassAvg, 2) }}</div>
                        <div class="stat-subtext">marks per lesson</div>
                    </div>
                    <div class="comparison-item">
                        <div class="comparison-label">Performance Status</div>
                        <div class="comparison-value {{ $overallStudentAvg > $overallClassAvg ? 'better' : ($overallStudentAvg < $overallClassAvg ? 'worse' : 'equal') }}">
                            @if($overallStudentAvg > $overallClassAvg)
                                📈 Above Average
                            @elseif($overallStudentAvg < $overallClassAvg)
                                📉 Below Average
                            @else
                                📊 At Average
                            @endif
                        </div>
                        <div class="stat-subtext">
                            @if($overallStudentAvg > $overallClassAvg)
                                Performing well
                            @elseif($overallStudentAvg < $overallClassAvg)
                                Needs improvement
                            @else
                                On track
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
