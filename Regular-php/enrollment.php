<?php
// --- PHP Logic: Read and Update Data ---
$data_file = 'available_lessons.json';
$json_data = file_get_contents($data_file);
$lessons = json_decode($json_data, true);

$message = '';

// Handle Enrollment Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_id'])) {
    $enroll_id = $_POST['enroll_id'];
    
    foreach ($lessons as &$lesson) {
        if ($lesson['lesson_id'] == $enroll_id) {
            if (!$lesson['is_mandatory']) {
                $lesson['enrolled'] = true;
                $message = "Success! You have enrolled in: " . $lesson['title'];
            }
            break;
        }
    }
    // Save changes back to JSON mock DB
    file_put_contents($data_file, json_encode($lessons, JSON_PRETTY_PRINT));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment - CompuPlay</title>
    <style>
        /* CompuPlay Theme */
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .header { background-color: #2454FF; color: white; padding: 20px; text-align: center; }
        .container { max-width: 900px; margin: 30px auto; padding: 20px; }
        
        /* Success Message */
        .alert { padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px; }

        /* Card Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        
        .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; border-top: 4px solid #2454FF; }
        .card.mandatory { border-top-color: #E92222; } /* Red top for mandatory */
        
        .card h3 { margin-top: 0; color: #333; font-size: 1.2em; }
        .topic { color: #666; font-size: 0.9em; margin-bottom: 10px; display: block; }
        .duration { font-weight: bold; color: #5FAD56; font-size: 0.9em; }
        
        .badge { 
            position: absolute; top: 15px; right: 15px; 
            padding: 4px 8px; border-radius: 12px; font-size: 0.75em; font-weight: bold;
            color: white;
        }
        .bg-green { background-color: #5FAD56; }
        .bg-blue { background-color: #2454FF; }
        .bg-grey { background-color: #999; }

        /* Enroll Button */
        .btn-enroll { 
            display: block; width: 100%; padding: 10px; margin-top: 15px;
            background-color: #2454FF; color: white; border: none; border-radius: 4px;
            cursor: pointer; text-align: center; text-decoration: none; font-weight: bold;
        }
        .btn-enroll:hover { background-color: #1a3aab; }
        .btn-disabled { background-color: #ccc; cursor: default; color: #666; }
        
    </style>
</head>
<body>

    <div class="header">
        <h1>Course Catalog</h1>
        <p>Explore and enroll in extra learning materials.</p>
    </div>

    <div class="container">
        
        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="grid">
            <?php foreach ($lessons as $lesson): ?>
                <div class="card <?php echo $lesson['is_mandatory'] ? 'mandatory' : ''; ?>">
                    
                    <!-- Status Badge -->
                    <?php if ($lesson['enrolled']): ?>
                        <span class="badge bg-green">Enrolled</span>
                    <?php elseif ($lesson['is_mandatory']): ?>
                        <span class="badge bg-grey">Mandatory</span>
                    <?php else: ?>
                        <span class="badge bg-blue">Optional</span>
                    <?php endif; ?>

                    <h3><?php echo $lesson['title']; ?></h3>
                    <span class="topic">Topic: <?php echo $lesson['topic']; ?></span>
                    <div class="duration"><i class="far fa-clock"></i> <?php echo $lesson['duration']; ?> mins</div>
                    
                    <!-- Action Button -->
                    <form method="POST">
                        <input type="hidden" name="enroll_id" value="<?php echo $lesson['lesson_id']; ?>">
                        
                        <?php if ($lesson['enrolled']): ?>
                            <button type="button" class="btn-enroll btn-disabled" disabled>Already Enrolled</button>
                        <?php else: ?>
                            <button type="submit" class="btn-enroll">Enroll Now</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>