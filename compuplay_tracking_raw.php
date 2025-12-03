<?php
// Simple standalone demo page for CompuPlay Tracking (raw PHP + CSS + JSON-like data)

$lang = $_GET['lang'] ?? 'en';
$page = $_GET['page'] ?? 'dashboard';

// Dummy Malay HCI lessons
$lessons = [
    [
        'id' => 1,
        'title_ms' => 'Pengenalan Interaksi Manusia–Komputer',
        'title_en' => 'Introduction to Human–Computer Interaction',
    ],
    [
        'id' => 2,
        'title_ms' => 'Prinsip Reka Bentuk Antara Muka',
        'title_en' => 'Interface Design Principles',
    ],
];

// Dummy students with scores (out of 3)
$students = [
    [
        'name' => 'Ali Ahmad',
        'class' => '5A',
        'scores' => [1 => 3, 2 => 1], // lesson_id => marks
    ],
    [
        'name' => 'Siti Rahman',
        'class' => '5A',
        'scores' => [1 => 0, 2 => 2],
    ],
];

function t($en, $ms, $lang)
{
    return $lang === 'ms' ? $ms : $en;
}

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('CompuPlay Tracking (Raw Demo)', 'Demo Penjejakan CompuPlay (Mentah)', $lang) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background:#f5f7fa;
            color:#111827;
            line-height:1.6;
        }
        .navbar {
            background:#ffffff;
            border-bottom:1px solid #e5e7eb;
            padding:1rem 2rem;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:0 1px 3px rgba(0,0,0,0.05);
        }
        .logo {
            width:40px;height:40px;
            border-radius:8px;
            background:linear-gradient(135deg,#3b82f6 0%,#60a5fa 100%);
            display:flex;align-items:center;justify-content:center;
            color:#fff;font-weight:700;font-size:1.25rem;
        }
        .logo-text { margin-left:0.75rem;font-weight:700;font-size:1.25rem; }
        .logo-wrap { display:flex;align-items:center; }
        .nav-links { display:flex;gap:1.5rem;align-items:center; }
        .nav-link {
            text-decoration:none;
            color:#6b7280;
            font-weight:500;
            padding-bottom:0.25rem;
        }
        .nav-link.active {
            color:#3b82f6;
            border-bottom:2px solid #3b82f6;
        }
        .container { max-width:1200px;margin:2rem auto;padding:0 1.5rem; }
        h1 { font-size:1.75rem;margin-bottom:0.5rem; }
        p.subtitle { color:#6b7280;margin-bottom:1.25rem; }
        table { width:100%;border-collapse:collapse;margin-top:1rem;background:#fff;border-radius:0.75rem;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08); }
        th, td { padding:0.75rem 1rem;font-size:0.875rem;border-bottom:1px solid #e5e7eb;text-align:left; }
        th { background:#f9fafb;font-weight:600;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#f9fafb; }
        .flag { color:#ef4444;font-weight:700;margin-left:0.25rem; }
        .status-badge {
            display:inline-block;
            padding:0.15rem 0.6rem;
            border-radius:9999px;
            font-size:0.7rem;
            font-weight:600;
        }
        .status-ok { background:#d1fae5;color:#065f46; }
        .status-low { background:#fee2e2;color:#991b1b; }
        .card {
            background:#fff;
            border-radius:0.75rem;
            padding:1.5rem;
            box-shadow:0 1px 3px rgba(0,0,0,0.08);
            margin-bottom:1.5rem;
        }
        .lang-toggle {
            position:fixed;
            right:1.5rem;
            bottom:1.5rem;
            width:44px;
            height:44px;
            border-radius:9999px;
            background:#111827;
            color:#ffffff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:0.75rem;
            font-weight:600;
            text-decoration:none;
            box-shadow:0 10px 15px rgba(0,0,0,0.25);
            cursor:pointer;
            z-index:50;
        }
        .lang-toggle:hover { background:#1f2937; }
        .bell {
            margin-left:1rem;
            font-size:1.1rem;
        }
        .tabs { display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap; }
        .tab {
            text-decoration:none;
            font-size:0.85rem;
            padding:0.35rem 0.9rem;
            border-radius:9999px;
            border:1px solid #e5e7eb;
            color:#374151;
        }
        .tab.active { background:#3b82f6;border-color:#3b82f6;color:#fff; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-wrap">
            <div class="logo">C</div>
            <div class="logo-text">CompuPlay</div>
        </div>
        <div class="nav-links">
            <?php
            $pages = [
                'dashboard' => ['en' => 'Dashboard', 'ms' => 'Papan Pemuka'],
                'performance' => ['en' => 'View Performance', 'ms' => 'Lihat Prestasi'],
                'progress' => ['en' => 'View Progress', 'ms' => 'Lihat Perkembangan'],
                'activities' => ['en' => 'Manage Activities', 'ms' => 'Mengendalikan Aktiviti'],
                'notifications' => ['en' => 'Notifications', 'ms' => 'Notifikasi'],
            ];
            foreach ($pages as $key => $labels):
                $url = '?page=' . $key . '&lang=' . $lang;
                $active = $page === $key ? 'active' : '';
            ?>
                <a class="nav-link <?= $active ?>" href="<?= $url ?>"><?= t($labels['en'], $labels['ms'], $lang) ?></a>
            <?php endforeach; ?>
            <span class="bell">🔔</span>
        </div>
    </nav>

    <div class="container">
        <?php if ($page === 'dashboard'): ?>
            <h1><?= t('Welcome back!', 'Selamat kembali!', $lang) ?></h1>
            <p class="subtitle"><?= t('Quick overview of lessons and performance.', 'Gambaran ringkas pelajaran dan prestasi.', $lang) ?></p>
            <div class="card">
                <h2 style="margin-bottom:0.75rem;"><?= t('Recent HCI Lessons', 'Pelajaran HCI Terkini', $lang) ?></h2>
                <ul>
                    <?php foreach ($lessons as $lesson): ?>
                        <li><?= $lang === 'ms' ? $lesson['title_ms'] : $lesson['title_en'] ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($page === 'performance'): ?>
            <h1><?= t('Performance Table', 'Jadual Prestasi', $lang) ?></h1>
            <p class="subtitle"><?= t('Scores per lesson (out of 3).', 'Markah setiap pelajaran (daripada 3).', $lang) ?></p>
            <table>
                <thead>
                    <tr>
                        <th><?= t('Student', 'Pelajar', $lang) ?></th>
                        <th><?= t('Class', 'Kelas', $lang) ?></th>
                        <?php foreach ($lessons as $lesson): ?>
                            <th><?= $lang === 'ms' ? $lesson['title_ms'] : $lesson['title_en'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <?php
                        $hasLow = false;
                        foreach ($lessons as $lesson) {
                            $score = $s['scores'][$lesson['id']] ?? null;
                            if ($score !== null && ($score / 3) * 100 <= 20) {
                                $hasLow = true;
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($s['name']) ?>
                                <?php if ($hasLow): ?>
                                    <span class="flag">🚩</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['class']) ?></td>
                            <?php foreach ($lessons as $lesson): ?>
                                <?php
                                $score = $s['scores'][$lesson['id']] ?? null;
                                $percent = $score !== null ? ($score / 3) * 100 : null;
                                $low = $percent !== null && $percent <= 20;
                                ?>
                                <td>
                                    <?php if ($score === null): ?>
                                        -
                                    <?php else: ?>
                                        <?= $score ?>/3
                                        <span class="status-badge <?= $low ? 'status-low' : 'status-ok' ?>">
                                            <?= $low ? t('Low', 'Rendah', $lang) : t('OK', 'Baik', $lang) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($page === 'progress'): ?>
            <h1><?= t('Lesson Progress', 'Perkembangan Pelajaran', $lang) ?></h1>
            <p class="subtitle"><?= t('Simple percentage of completed lessons.', 'Peratus pelajaran yang telah disiapkan.', $lang) ?></p>
            <table>
                <thead>
                    <tr>
                        <th><?= t('Student', 'Pelajar', $lang) ?></th>
                        <th><?= t('Completed', 'Selesai', $lang) ?></th>
                        <th><?= t('Completion %', 'Peratus Siap', $lang) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <?php
                        $completed = count($s['scores']);
                        $total = count($lessons);
                        $pct = $total ? round(($completed / $total) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= $completed ?>/<?= $total ?></td>
                            <td><?= $pct ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($page === 'activities'): ?>
            <h1><?= t('Manage Activities', 'Mengendalikan Aktiviti', $lang) ?></h1>
            <p class="subtitle"><?= t('Dummy calendar-style list of upcoming HCI lessons.', 'Senarai ringkas pelajaran HCI yang akan datang.', $lang) ?></p>
            <div class="card">
                <h2 style="margin-bottom:0.75rem;"><?= t('Upcoming Due Dates', 'Tarikh Akhir Akan Datang', $lang) ?></h2>
                <ul>
                    <li><?= t('Next week', 'Minggu hadapan', $lang) ?> – <?= $lang === 'ms' ? $lessons[0]['title_ms'] : $lessons[0]['title_en'] ?></li>
                    <li><?= t('In two weeks', 'Dalam dua minggu', $lang) ?> – <?= $lang === 'ms' ? $lessons[1]['title_ms'] : $lessons[1]['title_en'] ?></li>
                </ul>
            </div>
        <?php elseif ($page === 'notifications'): ?>
            <h1><?= t('Notifications', 'Notifikasi', $lang) ?></h1>
            <p class="subtitle"><?= t('Students below 20% are flagged.', 'Pelajar di bawah 20% akan ditandakan.', $lang) ?></p>
            <div class="card">
                <?php
                $alerts = [];
                foreach ($students as $s) {
                    foreach ($lessons as $lesson) {
                        $score = $s['scores'][$lesson['id']] ?? null;
                        if ($score === null) continue;
                        $percent = ($score / 3) * 100;
                        if ($percent <= 20) {
                            $alerts[] = [
                                'student' => $s['name'],
                                'class' => $s['class'],
                                'lesson' => $lang === 'ms' ? $lesson['title_ms'] : $lesson['title_en'],
                                'percent' => $percent,
                            ];
                        }
                    }
                }
                if (!$alerts): ?>
                    <p><?= t('No alerts at the moment.', 'Tiada amaran buat masa ini.', $lang) ?></p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($alerts as $a): ?>
                            <li>
                                🚩 <?= htmlspecialchars($a['student']) ?> (<?= htmlspecialchars($a['class']) ?>)
                                – <?= htmlspecialchars($a['lesson']) ?>: <?= $a['percent'] ?>%
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <a class="lang-toggle" href="?page=<?= htmlspecialchars($page) ?>&lang=<?= $lang === 'en' ? 'ms' : 'en' ?>">
        <?= $lang === 'en' ? 'BM' : 'EN' ?>
    </a>
</body>
</html>


