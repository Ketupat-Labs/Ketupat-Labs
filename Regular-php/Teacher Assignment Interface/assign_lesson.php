<?php
// --- CONFIGURATION ---
$classes_file = 'classes.json';
$lessons_file = 'lessons.json';
$assignments_file = 'assignments.json';

// --- 1. READ DATA ---
// In a real app, these would be database queries (SELECT * FROM classes...)
$classes = json_decode(file_get_contents($classes_file), true);
$lessons = json_decode(file_get_contents($lessons_file), true);

$message = "";

// --- 2. HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_class_id = $_POST['class_id'] ?? '';
    $selected_lessons = $_POST['lessons'] ?? [];

    if (empty($selected_class_id) || empty($selected_lessons)) {
        $message = "<div style='color: #721c24; background-color: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px;'>Error: Please select a class and at least one lesson.</div>";
    } else {
        // Get existing assignments
        $current_assignments = json_decode(file_get_contents($assignments_file), true);
        if (!is_array($current_assignments)) $current_assignments = [];

        // Find Class Name (for display purposes)
        $class_name = "Unknown";
        foreach ($classes as $cls) {
            if ($cls['id'] == $selected_class_id) $class_name = $cls['name'];
        }

        // Create assignment records for each selected lesson
        $count = 0;
        foreach ($selected_lessons as $lesson_id) {
            // Find Lesson Title
            $lesson_title = "Unknown";
            foreach ($lessons as $l) {
                if ($l['id'] == $lesson_id) $lesson_title = $l['title'];
            }

            // The new assignment record (M6 Data Structure)
            $new_record = [
                "assignment_id" => uniqid(),
                "class_id" => $selected_class_id,
                "class_name" => $class_name,
                "lesson_id" => $lesson_id,
                "lesson_title" => $lesson_title,
                "type" => "Mandatory", // Explicitly marking as mandatory per User Story
                "assigned_date" => date("Y-m-d H:i:s")
            ];

            $current_assignments[] = $new_record;
            $count++;
        }

        // Save back to JSON (Simulating DB Insert)
        file_put_contents($assignments_file, json_encode($current_assignments, JSON_PRETTY_PRINT));
        
        $message = "<div style='color: #155724; background-color: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;'>Success! Assigned $count mandatory lesson(s) to Class $class_name.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher - Assign Lessons</title>
    <style>
        /* CompuPlay Theme Styles */
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f9; padding: 0; margin: 0; color: #333; }
        
        .header { background-color: #2454FF; color: white; padding: 20px 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 1.5em; }
        
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        h2 { border-bottom: 2px solid #2454FF; padding-bottom: 10px; color: #2454FF; margin-top: 0; }
        
        label { display: block; margin-top: 20px; font-weight: 600; font-size: 1.1em; color: #3E3E3E; margin-bottom: 8px; }
        
        select { width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 1em; background-color: #fff; }
        
        /* Checkbox List Styling */
        .checkbox-group { margin-top: 10px; border: 1px solid #e0e0e0; padding: 15px; max-height: 250px; overflow-y: auto; border-radius: 6px; background-color: #fafafa; }
        .checkbox-item { display: flex; align-items: center; margin-bottom: 12px; padding: 8px; background: white; border: 1px solid #eee; border-radius: 4px; transition: background 0.2s; }
        .checkbox-item:hover { background-color: #f0f7ff; border-color: #2454FF; }
        .checkbox-item input { margin-right: 12px; transform: scale(1.2); cursor: pointer; }
        
        button { 
            width: 100%; padding: 14px; margin-top: 30px; 
            background-color: #5FAD56; color: white; border: none; border-radius: 6px; 
            cursor: pointer; font-weight: bold; font-size: 1.1em; transition: background 0.3s; 
        }
        button:hover { background-color: #4caf50; }
        
        .log-section { margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px; }
        .log-item { background: #f9f9f9; padding: 10px; margin-bottom: 5px; border-left: 4px solid #2454FF; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="header">
    <h1>Teacher Dashboard</h1>
</div>

<div class="container">
    <h2>Assign Mandatory Lessons (UC006)</h2>
    <p>Select a class and the lessons to ensure all students cover mandatory topics.</p>
    
    <?php echo $message; ?>

    <form method="POST">
        <!-- 1. Select Class -->
        <label for="class_id">1. Select Target Class:</label>
        <select name="class_id" id="class_id" required>
            <option value="">-- Choose Class --</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?php echo $c['id']; ?>">
                    <?php echo $c['name']; ?> (Subject: <?php echo $c['subject']; ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <!-- 2. Select Lessons -->
        <label>2. Select Lessons to Assign:</label>
        <div class="checkbox-group">
            <?php foreach ($lessons as $l): ?>
                <div class="checkbox-item">
                    <input type="checkbox" name="lessons[]" value="<?php echo $l['id']; ?>" id="lesson_<?php echo $l['id']; ?>">
                    <label for="lesson_<?php echo $l['id']; ?>" style="font-weight: normal; margin:0; cursor:pointer; width:100%;">
                        <strong style="color: #333;"><?php echo $l['title']; ?></strong> 
                        <br><span style="color: #777; font-size: 0.9em;">Topic: <?php echo $l['topic']; ?></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit">Assign Selected Lessons</button>
    </form>

    <!-- 3. View Assignments Log (To prove it works) -->
    <div class="log-section">
        <h3 style="color: #3E3E3E;">Recent Assignment Log</h3>
        <?php 
        $log = json_decode(file_get_contents($assignments_file), true);
        if (!empty($log)) {
            // Show last 5 assignments
            $recent_logs = array_slice(array_reverse($log), 0, 5);
            foreach ($recent_logs as $entry) {
                echo "<div class='log-item'>
                        <strong>{$entry['lesson_title']}</strong> assigned to <strong>{$entry['class_name']}</strong>
                        <span style='float:right; color:#999; font-size:0.8em;'>{$entry['assigned_date']}</span>
                      </div>";
            }
        } else {
            echo "<p style='color: #999; font-style: italic;'>No assignments recorded yet.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>