<?php
session_start();

// --- 1. DATA SIMULATION (JSON/Array) ---
// This replaces the database. In a real app, this would be in a DB.
$data = [
    'students' => [
        ['id' => 1, 'name' => 'Ahmad Albab', 'class' => '5A'],
        ['id' => 2, 'name' => 'Siti Nurhaliza', 'class' => '5A'],
        ['id' => 3, 'name' => 'Chong Wei', 'class' => '5A'],
        ['id' => 4, 'name' => 'Muthusamy', 'class' => '5B'],
        ['id' => 5, 'name' => 'Nurul Izzah', 'class' => '5B'],
        ['id' => 6, 'name' => 'Adam Haikal', 'class' => '5A'],
        ['id' => 7, 'name' => 'Tan Mei Ling', 'class' => '5B'],
        ['id' => 8, 'name' => 'Raju', 'class' => '5B'],
    ],
    'lessons' => [
        ['id' => 1, 'title' => 'Pengenalan kepada HCI', 'class' => '5A', 'q1' => 'Pengenalan HCI', 'total_questions' => 3],
        ['id' => 2, 'title' => 'Prinsip Rekabentuk UI', 'class' => '5A', 'q1' => 'Prinsip UI', 'total_questions' => 3],
        ['id' => 3, 'title' => 'Kebolehgunaan (Usability)', 'class' => '5A', 'q1' => 'Kebolehgunaan', 'total_questions' => 3],
        ['id' => 4, 'title' => 'Pengenalan kepada HCI', 'class' => '5B', 'q1' => 'Pengenalan HCI', 'total_questions' => 3],
        ['id' => 5, 'title' => 'Prototaip dan Penilaian', 'class' => '5B', 'q1' => 'Prototaip', 'total_questions' => 3],
    ],
    // We will generate answers dynamically if not set in session to keep it persistent for the session
];

if (!isset($_SESSION['student_answers'])) {
    $answers = [];
    foreach ($data['students'] as $student) {
        foreach ($data['lessons'] as $lesson) {
            if ($student['class'] === $lesson['class']) {
                // Randomize marks
                // Force some failures (below 20% i.e., 0 marks)
                $forceFail = rand(0, 10) > 8; 
                $totalMarks = 0;
                $qAnswers = [];
                
                for ($i = 1; $i <= 3; $i++) {
                    if ($forceFail) {
                        $isCorrect = false;
                    } else {
                        $isCorrect = rand(0, 10) > 3;
                    }
                    $qAnswers["q$i"] = $isCorrect;
                    if ($isCorrect) $totalMarks++;
                }
                
                $answers[] = [
                    'student_id' => $student['id'],
                    'lesson_id' => $lesson['id'],
                    'answers' => $qAnswers,
                    'total_marks' => $totalMarks,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(0, 10) . ' days'))
                ];
            }
        }
    }
    $_SESSION['student_answers'] = $answers;
}

$studentAnswers = $_SESSION['student_answers'];

// --- 2. LOGIC & ROUTING ---
$page = $_GET['page'] ?? 'dashboard';
$lang = $_GET['lang'] ?? 'en';

// Helper for translations
function t($en, $ms) {
    global $lang;
    return $lang === 'en' ? $en : $ms;
}

// Helper to get notifications
function getNotifications($answers, $students, $lessons) {
    $notifs = [];
    foreach ($answers as $ans) {
        $percentage = ($ans['total_marks'] / 3) * 100;
        if ($percentage <= 20) {
            $student = null;
            foreach($students as $s) { if($s['id'] == $ans['student_id']) $student = $s; }
            
            $lesson = null;
            foreach($lessons as $l) { if($l['id'] == $ans['lesson_id']) $lesson = $l; }
            
            if($student && $lesson) {
                $notifs[] = [
                    'student' => $student,
                    'lesson' => $lesson,
                    'score' => $ans['total_marks'],
                    'percentage' => round($percentage, 1),
                    'created_at' => $ans['created_at']
                ];
            }
        }
    }
    // Sort by date desc
    usort($notifs, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    return $notifs;
}

$notifications = getNotifications($studentAnswers, $data['students'], $data['lessons']);
$hasNotifications = count($notifications) > 0;

// --- 3. VIEW RENDERING ---
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CompuPlay Tracking</title>
    <style>
        /* CSS from dashboard.blade.php */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f7fa; color: #1a1a1a; line-height: 1.6; }
        .navbar { background-color: #ffffff; border-bottom: 1px solid #e5e7eb; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }
        .logo-container { display: flex; align-items: center; gap: 0.75rem; }
        .logo { width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 24px; color: white; }
        .logo-text { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; }
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-link { text-decoration: none; color: #6b7280; font-weight: 500; transition: color 0.2s; padding: 0.5rem 0; }
        .nav-link.active { color: #3b82f6; border-bottom: 2px solid #3b82f6; }
        .nav-link:hover { color: #3b82f6; }
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        
        /* Cards & Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .stat-icon.blue { background-color: #dbeafe; color: #3b82f6; }
        .stat-icon.green { background-color: #d1fae5; color: #10b981; }
        .stat-icon.orange { background-color: #fed7aa; color: #f97316; }
        .stat-icon.yellow { background-color: #fef3c7; color: #f59e0b; }
        .stat-content { flex: 1; }
        .stat-label { font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem; }
        .stat-value { font-size: 1.875rem; font-weight: 700; color: #1a1a1a; }
        
        /* Tables */
        .table-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); overflow: hidden; margin-bottom: 1.5rem; }
        .table-header { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 1rem 1.5rem; font-weight: 600; font-size: 1rem; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f9fafb; color: #1a1a1a; padding: 1rem 1.5rem; text-align: left; font-weight: 600; font-size: 0.875rem; border-bottom: 2px solid #e5e7eb; }
        td { padding: 1rem 1.5rem; border-bottom: 1px solid #f3f4f6; font-size: 0.875rem; }
        tr:hover { background-color: #f9fafb; }
        
        /* Notifications */
        .notification-item { padding: 1.5rem; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 1rem; }
        .notification-icon { width: 48px; height: 48px; background: #fee2e2; color: #991b1b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
        
        /* Toggle & Badge */
        .lang-toggle { position: fixed; right: 1.5rem; bottom: 1.5rem; width: 50px; height: 50px; border-radius: 9999px; background: #111827; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 600; text-decoration: none; box-shadow: 0 10px 15px rgba(0,0,0,0.25); cursor: pointer; z-index: 50; transition: transform 0.2s; }
        .lang-toggle:hover { background: #1f2937; transform: scale(1.05); }
        .notification-badge { position: relative; display: inline-flex; align-items: center; }
        .badge-dot { position: absolute; top: 0; right: 0; width: 8px; height: 8px; background-color: #ef4444; border-radius: 50%; border: 2px solid white; }
        
        .flag-icon { color: #ef4444; font-size: 1.25rem; margin-left: 0.5rem; cursor: help; }
        .correct { color: #10b981; font-weight: 600; }
        .incorrect { color: #ef4444; font-weight: 600; }
        
        /* Quick Access */
        .quick-access { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .quick-card { border-radius: 16px; padding: 2rem; color: white; display: flex; flex-direction: column; justify-content: space-between; min-height: 200px; text-decoration: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .quick-card.blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .quick-card.green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .quick-card.orange { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <a href="?page=dashboard&lang=<?= $lang ?>" style="text-decoration:none; display:flex; gap:0.75rem; align-items:center;">
                <div class="logo">C</div>
                <div class="logo-text">CompuPlay</div>
            </a>
        </div>
        <div class="nav-links">
            <a href="?page=dashboard&lang=<?= $lang ?>" class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>"><?= t('Dashboard', 'Papan Pemuka') ?></a>
            <a href="?page=performance&lang=<?= $lang ?>" class="nav-link <?= $page === 'performance' ? 'active' : '' ?>"><?= t('Track Student', 'Lihat Prestasi') ?></a>
            <a href="?page=progress&lang=<?= $lang ?>" class="nav-link <?= $page === 'progress' ? 'active' : '' ?>"><?= t('View Progress', 'Lihat Perkembangan') ?></a>
            <a href="?page=manage&lang=<?= $lang ?>" class="nav-link <?= $page === 'manage' ? 'active' : '' ?>"><?= t('Manage Activities', 'Mengendalikan Aktiviti') ?></a>
            <a href="?page=notifications&lang=<?= $lang ?>" class="nav-link notification-badge <?= $page === 'notifications' ? 'active' : '' ?>">
                <span style="font-size: 1.25rem;">🔔</span>
                <?php if($hasNotifications): ?><span class="badge-dot"></span><?php endif; ?>
            </a>
        </div>
        <div>Test User ▼</div>
    </nav>

    <div class="container">
        <?php if ($page === 'dashboard'): ?>
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; font-weight: 700;"><?= t('Welcome back', 'Selamat kembali') ?>, Test User!</h1>
                <p style="color: #6b7280;"><?= t('Continue your learning journey.', 'Teruskan perjalanan pembelajaran anda.') ?></p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">📚</div>
                    <div class="stat-content">
                        <div class="stat-label"><?= t('Published Lessons', 'Pelajaran Diterbitkan') ?></div>
                        <div class="stat-value">5</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">📄</div>
                    <div class="stat-content">
                        <div class="stat-label"><?= t('Your Lessons', 'Pelajaran Anda') ?></div>
                        <div class="stat-value">3</div>
                    </div>
                </div>
            </div>
            
            <div class="quick-access">
                <a href="?page=performance&lang=<?= $lang ?>" class="quick-card blue">
                    <div><h3 style="font-size:1.5rem; margin-bottom:0.5rem;"><?= t('Track Student', 'Lihat Prestasi') ?></h3><p><?= t('Monitor and analyze student performance', 'Pantau dan analisis prestasi pelajar') ?></p></div>
                    <div style="margin-top:1rem;">→</div>
                </a>
                <div class="quick-card green">
                    <div><h3 style="font-size:1.5rem; margin-bottom:0.5rem;">Manage Lessons</h3><p>Create and manage content</p></div>
                    <div style="margin-top:1rem;">→</div>
                </div>
            </div>

        <?php elseif ($page === 'performance'): ?>
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; font-weight: 700;"><?= t('Student Performance Tracking', 'Jadual Prestasi Pelajar') ?></h1>
                <p style="color: #6b7280;"><?= t('Monitor and analyze student performance across lessons', 'Pantau dan analisis prestasi pelajar merentasi pelajaran') ?></p>
            </div>
            
            <div class="table-card">
                <div class="table-header"><?= t('Performance Table', 'Jadual Prestasi') ?></div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th><?= t('Student Name', 'Nama Pelajar') ?></th>
                                <th><?= t('Class', 'Kelas') ?></th>
                                <?php foreach($data['lessons'] as $l): ?>
                                    <th><?= $l['q1'] ?></th>
                                <?php endforeach; ?>
                                <th><?= t('Overall Avg', 'Purata Keseluruhan') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['students'] as $student): ?>
                                <tr>
                                    <td>
                                        👤 <?= $student['name'] ?>
                                        <?php 
                                            // Check for low score
                                            $hasLow = false;
                                            foreach($studentAnswers as $ans) {
                                                if($ans['student_id'] == $student['id'] && ($ans['total_marks']/3)*100 <= 20) {
                                                    $hasLow = true; break;
                                                }
                                            }
                                            if($hasLow) echo '<span class="flag-icon" title="Below 20%">🚩</span>';
                                        ?>
                                    </td>
                                    <td><?= $student['class'] ?></td>
                                    <?php 
                                        $totalScore = 0; $count = 0;
                                        foreach($data['lessons'] as $lesson): 
                                            // Find answer
                                            $myAns = null;
                                            foreach($studentAnswers as $ans) {
                                                if($ans['student_id'] == $student['id'] && $ans['lesson_id'] == $lesson['id']) {
                                                    $myAns = $ans; break;
                                                }
                                            }
                                    ?>
                                        <td class="<?= $myAns ? ($myAns['total_marks'] > 0 ? 'correct' : 'incorrect') : '' ?>">
                                            <?php if($myAns): 
                                                $totalScore += $myAns['total_marks']; $count++;
                                                echo $myAns['total_marks'] . '/3';
                                            else: echo '-'; endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td><strong><?= $count > 0 ? number_format($totalScore/$count, 1) : '-' ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'notifications'): ?>
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; font-weight: 700;"><?= t('Performance Alerts', 'Amaran Prestasi') ?></h1>
                <p style="color: #6b7280;"><?= t('Notifications for students scoring 20% and below', 'Notifikasi untuk pelajar yang mendapat markah 20% dan ke bawah') ?></p>
            </div>
            
            <div class="table-card">
                <div class="table-header">🚩 <?= t('Low Performance Alerts', 'Amaran Prestasi Rendah') ?></div>
                <?php if(empty($notifications)): ?>
                    <div style="padding:3rem; text-align:center; color:#6b7280;">
                        <p>🎉 <?= t('No low performance alerts at this time.', 'Tiada amaran prestasi rendah pada masa ini.') ?></p>
                        <p><?= t('All students are performing above 20%.', 'Semua pelajar menunjukkan prestasi melebihi 20%.') ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach($notifications as $notif): ?>
                        <div class="notification-item">
                            <div class="notification-icon">🚩</div>
                            <div style="flex:1;">
                                <div style="font-weight:600;"><?= $notif['student']['name'] ?> - Class <?= $notif['student']['class'] ?></div>
                                <div style="color:#6b7280;">Scored <?= $notif['score'] ?>/3 (<?= $notif['percentage'] ?>%) in <?= $notif['lesson']['title'] ?></div>
                                <div style="font-size:0.75rem; color:#9ca3af;"><?= $notif['created_at'] ?></div>
                            </div>
                            <a href="?page=progress&lang=<?= $lang ?>" style="color:#3b82f6; text-decoration:none; font-weight:500;">
                                <?= t('View Progress', 'Lihat Perkembangan') ?> →
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
        <?php elseif ($page === 'progress'): ?>
             <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; font-weight: 700;"><?= t('Lesson Progress Tracking', 'Jejak Perkembangan Pelajaran') ?></h1>
                <p style="color: #6b7280;"><?= t('Monitor student completion status across all lessons', 'Pantau status penyelesaian pelajar merentasi semua pelajaran') ?></p>
            </div>
            
            <div class="table-card">
                <div class="table-header">📊 <?= t('Lesson Progress Table', 'Jadual Perkembangan Pelajaran') ?></div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th><?= t('Student Name', 'Nama Pelajar') ?></th>
                                <?php foreach($data['lessons'] as $l): ?>
                                    <th><?= $l['q1'] ?></th>
                                <?php endforeach; ?>
                                <th><?= t('Completion %', 'Peratus Penyelesaian') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['students'] as $student): 
                                $completedCount = 0;
                            ?>
                                <tr>
                                    <td>
                                        👤 <?= $student['name'] ?>
                                        <?php 
                                            $hasLow = false;
                                            foreach($studentAnswers as $ans) {
                                                if($ans['student_id'] == $student['id'] && ($ans['total_marks']/3)*100 <= 20) {
                                                    $hasLow = true; break;
                                                }
                                            }
                                            if($hasLow) echo '<span class="flag-icon" title="Below 20%">🚩</span>';
                                        ?>
                                    </td>
                                    <?php foreach($data['lessons'] as $lesson): 
                                        $myAns = null;
                                        foreach($studentAnswers as $ans) {
                                            if($ans['student_id'] == $student['id'] && $ans['lesson_id'] == $lesson['id']) {
                                                $myAns = $ans; break;
                                            }
                                        }
                                        if($myAns) $completedCount++;
                                        
                                        $status = 'Not Started';
                                        $color = '#f3f4f6'; $textColor = '#6b7280';
                                        
                                        if($myAns) {
                                            if(($myAns['total_marks']/3)*100 <= 20) {
                                                $status = 'Completed (Low Score)';
                                                $color = '#fee2e2'; $textColor = '#991b1b';
                                            } else {
                                                $status = 'Completed';
                                                $color = '#d1fae5'; $textColor = '#065f46';
                                            }
                                        }
                                        
                                        // Translate status
                                        if($lang !== 'en') {
                                            if($status === 'Completed') $status = 'Selesai';
                                            elseif($status === 'Completed (Low Score)') $status = 'Selesai (Markah Rendah)';
                                            elseif($status === 'Not Started') $status = 'Belum Mula';
                                        }
                                    ?>
                                        <td>
                                            <span style="display:inline-block; padding:0.25rem 0.75rem; border-radius:12px; font-size:0.75rem; font-weight:600; background:<?= $color ?>; color:<?= $textColor ?>;">
                                                <?= $status ?>
                                            </span>
                                        </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <strong><?= round(($completedCount / count($data['lessons'])) * 100) ?>%</strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <h1>Page not found</h1>
        <?php endif; ?>
    </div>

    <!-- Language Toggle -->
    <a href="?page=<?= $page ?>&lang=<?= $lang === 'en' ? 'ms' : 'en' ?>" class="lang-toggle">
        <?= $lang === 'en' ? 'BM' : 'EN' ?>
    </a>
</body>
</html>
