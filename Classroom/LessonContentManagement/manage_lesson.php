<?php
// --- PHP Logic: Mock Database Operations (CRUD) ---

// File path for mock database
$data_file = 'lessons.json';

// --- Function to safely read and write data ---
function get_lessons() {
    global $data_file;
    if (!file_exists($data_file)) {
        return [];
    }
    return json_decode(file_get_contents($data_file), true);
}

function save_lessons($lessons) {
    global $data_file;
    file_put_contents($data_file, json_encode($lessons, JSON_PRETTY_PRINT));
}

$lessons = get_lessons();
$action = $_GET['action'] ?? ''; // Default action is view/read

// --- C R E A T E (Add Lesson) Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $topic = $_POST['topic'] ?? '';
    
    if (!empty($title) && !empty($topic)) {
        $new_lesson = [
            'id' => time(), // Simple unique ID
            'title' => $title,
            'topic' => $topic,
            'duration' => $_POST['duration'] ?? 'N/A',
            'file' => $_FILES['material_file']['name'] ?? 'None' // Mock file name
        ];
        $lessons[] = $new_lesson;
        save_lessons($lessons);
        
        // Redirect to prevent form resubmission
        header('Location: manage_lessons.php');
        exit;
    }
}

// --- D E L E T E Logic ---
if ($action === 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];
    $lessons = array_filter($lessons, function($lesson) use ($id_to_delete) {
        return $lesson['id'] != $id_to_delete;
    });
    save_lessons(array_values($lessons)); // array_values resets keys after filter
    header('Location: manage_lessons.php');
    exit;
}

// --- U P D A T E (Edit Lesson) Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id_to_update = (int)$_POST['id'];
    $new_title = trim($_POST['title'] ?? '');
    $new_topic = $_POST['topic'] ?? '';

    foreach ($lessons as &$lesson) {
        if ($lesson['id'] === $id_to_update) {
            $lesson['title'] = $new_title;
            $lesson['topic'] = $new_topic;
            $lesson['duration'] = $_POST['duration'] ?? $lesson['duration'];
            // Mock file logic skipped for simplicity here
            break;
        }
    }
    save_lessons($lessons);
    header('Location: manage_lessons.php');
    exit;
}

// Check if we are showing the edit form
$edit_lesson = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id_to_edit = (int)$_GET['id'];
    foreach ($lessons as $lesson) {
        if ($lesson['id'] === $id_to_edit) {
            $edit_lesson = $lesson;
            break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CompuPlay - Manage Lessons</title>
    <style>
        /* --- Minimal CSS for Teacher View --- */
        body { font-family: Century Gothic, sans-serif; background-color: #f4f4f9; color: #3E3E3E; margin: 0; padding: 0; }
        .header { background-color: #2454FF; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .main-container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .card { background: #FFFFFF; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 30px; margin-bottom: 30px; border: 1px solid #e0e0e0; }
        .card h2 { color: #2454FF; border-bottom: 2px solid #2454FF; padding-bottom: 10px; margin-top: 0; margin-bottom: 20px; }
        
        /* Form Styling */
        label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: 600; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #969696; border-radius: 4px; box-sizing: border-box; }
        .required { color: #E92222; }
        
        /* Buttons and Table */
        button { background-color: #5FAD56; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; margin-top: 15px; }
        .btn-add { background-color: #2454FF; float: right; margin-bottom: 15px; }
        .btn-delete { background-color: #E92222; }
        .btn-edit { background-color: #FFBA08; }
        .btn-cancel { background-color: #969699; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f7f7f7; font-weight: 700; }
    </style>
    <script>
        // --- JavaScript: Confirmation and Toggle ---
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this lesson?")) {
                window.location.href = 'manage_lessons.php?action=delete&id=' + id;
            }
        }
    </script>
</head>
<body>

    <div class="header">
        <h1>Lesson Content Management</h1>
    </div>

    <div class="main-container">
        
        <?php if (!$edit_lesson): ?>
        
            <div class="card">
                <h2>Add New Lesson</h2>
                <form method="POST" action="manage_lessons.php?action=add" enctype="multipart/form-data">
                    
                    <label for="lessonTitle">Lesson Title <span class="required">*</span></label>
                    <input type="text" id="lessonTitle" name="title" required>

                    <label for="lessonTopic">Module / Topic <span class="required">*</span></label>
                    <select id="lessonTopic" name="topic" required>
                        <option value="">-- Select Topic --</option>
                        <option value="HCI">3.1 Interaction Design</option>
                        <option value="HCI_SCREEN">3.2 Screen Design</option>
                    </select>
                    
                    <label for="duration">Estimated Duration (Mins)</label>
                    <input type="number" id="duration" name="duration" min="5">

                    <label>Lesson Material (File Mock)</label>
                    <input type="file" name="material_file">
                    
                    <button type="submit">Save Lesson</button>
                </form>
            </div>

        <?php else: ?>

            <div class="card" style="border: 2px solid #FFBA08;">
                <h2>Edit Lesson: <?php echo htmlspecialchars($edit_lesson['title']); ?></h2>
                <form method="POST" action="manage_lessons.php?action=update">
                    <input type="hidden" name="id" value="<?php echo $edit_lesson['id']; ?>">
                    
                    <label for="lessonTitle">Lesson Title <span class="required">*</span></label>
                    <input type="text" id="lessonTitle" name="title" value="<?php echo htmlspecialchars($edit_lesson['title']); ?>" required>

                    <label for="lessonTopic">Module / Topic <span class="required">*</span></label>
                    <select id="lessonTopic" name="topic" required>
                        <?php 
                        $topics = ['HCI', 'HCI_SCREEN'];
                        foreach($topics as $topic):
                            $selected = ($edit_lesson['topic'] === $topic) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $topic; ?>" <?php echo $selected; ?>><?php echo $topic; ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="duration">Estimated Duration (Mins)</label>
                    <input type="number" id="duration" name="duration" min="5" value="<?php echo htmlspecialchars($edit_lesson['duration']); ?>">
                    
                    <button type="submit" style="background-color: #FFBA08;">Update Lesson</button>
                    <a href="manage_lessons.php" class="btn-cancel" style="padding: 10px; text-decoration: none;">Cancel</a>
                </form>
            </div>

        <?php endif; ?>

        <div class="card">
            <h2>Lessons Inventory</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Topic</th>
                        <th>Duration</th>
                        <th>File</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lessons)): ?>
                        <?php foreach ($lessons as $lesson): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                                <td><?php echo htmlspecialchars($lesson['topic']); ?></td>
                                <td><?php echo htmlspecialchars($lesson['duration']); ?> mins</td>
                                <td><?php echo htmlspecialchars($lesson['file']); ?></td>
                                <td>
                                    <a href="manage_lessons.php?action=edit&id=<?php echo $lesson['id']; ?>" class="btn-edit" style="text-decoration: none; padding: 5px 10px; color: white; margin-right: 5px;">Edit</a>
                                    <button class="btn-delete" onclick="confirmDelete(<?php echo $lesson['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center;">No lessons have been created yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>