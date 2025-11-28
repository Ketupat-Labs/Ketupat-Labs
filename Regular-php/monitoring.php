<?php
// --- PHP Logic: Read Data ---
$json_data = file_get_contents('monitoring_data.json');
$students = json_decode($json_data, true);

// Filter Logic (Optional: Simulate filtering by class if a query param exists)
$filter_class = $_GET['class'] ?? 'All';
if ($filter_class !== 'All') {
    $students = array_filter($students, function($s) use ($filter_class) {
        return $s['class'] === $filter_class;
    });
}

// Helper function to determine status color
function getStatusColor($status) {
    switch($status) {
        case 'Completed': return '#5FAD56'; // Green
        case 'In Progress': return '#2454FF'; // Blue
        case 'Stalled': return '#E92222'; // Red
        default: return '#969696'; // Grey
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Monitor - Student Progress</title>
    <style>
        /* CompuPlay Theme */
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .header { background-color: #2454FF; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1000px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; color: #333; }
        
        /* Filter Bar */
        .filter-bar { margin-bottom: 20px; text-align: right; }
        select { padding: 8px; border-radius: 4px; border: 1px solid #ddd; }
        
        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8f9fa; color: #333; font-weight: 600; text-align: left; padding: 12px; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; color: #555; }
        
        /* Progress Bar */
        .progress-container { background-color: #e0e0e0; border-radius: 10px; height: 10px; width: 100px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 10px; }
        
        /* Status Badge */
        .badge { padding: 5px 10px; border-radius: 12px; color: white; font-size: 0.85em; font-weight: bold; display: inline-block; }
        
        /* Action Button */
        .btn-guide { background-color: #FFBA08; color: #333; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.9em; text-decoration: none; }
        .btn-guide:hover { background-color: #e0a800; }
    </style>
</head>
<body>

    <div class="header">
        <h1>CompuPlay: Student Progress Monitor</h1>
        <span>Welcome, Cikgu Haziq</span>
    </div>

    <div class="container">
        <h2>Lesson Engagement Dashboard</h2>
        
        <div class="filter-bar">
            <form method="GET">
                <label>Filter by Class: </label>
                <select name="class" onchange="this.form.submit()">
                    <option value="All" <?php if($filter_class == 'All') echo 'selected'; ?>>All Classes</option>
                    <option value="4 Amanah" <?php if($filter_class == '4 Amanah') echo 'selected'; ?>>4 Amanah</option>
                    <option value="5 Bestari" <?php if($filter_class == '5 Bestari') echo 'selected'; ?>>5 Bestari</option>
                </select>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Lesson</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td style="font-weight: bold;"><?php echo $student['student_name']; ?></td>
                    <td><?php echo $student['class']; ?></td>
                    <td><?php echo $student['lesson_title']; ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="progress-container">
                                <div class="progress-fill" style="width: <?php echo $student['progress']; ?>%; background-color: <?php echo getStatusColor($student['status']); ?>;"></div>
                            </div>
                            <span><?php echo $student['progress']; ?>%</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background-color: <?php echo getStatusColor($student['status']); ?>;">
                            <?php echo $student['status']; ?>
                        </span>
                    </td>
                    <td><?php echo $student['last_accessed']; ?></td>
                    <td>
                        <!-- Provide Guidance Button only for Stalled/Low Progress students -->
                        <?php if ($student['status'] === 'Stalled' || $student['progress'] < 20): ?>
                            <a href="#" class="btn-guide" onclick="alert('Sending encouragement message to <?php echo $student['student_name']; ?>...')">Provide Guidance</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($students)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No records found for this class.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>