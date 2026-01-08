<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Bullet;

echo "=== TESTING X SLIDES PPTX GENERATION ===\n\n";

// Get the actual "x" slide data
$cacheKey = 'user_3_slide_sets';
$slideSets = Cache::get($cacheKey, []);

$xSlideSet = null;
foreach ($slideSets as $set) {
    if (isset($set['topic']) && strtolower($set['topic']) === 'x') {
        $xSlideSet = $set;
        break;
    }
}

if (!$xSlideSet) {
    die("❌ Could not find 'x' slide set\n");
}

echo "Found 'x' slide set with " . count($xSlideSet['slides']) . " slides\n\n";

try {
    $ppt = new PhpPresentation();
    $firstSlide = $ppt->getActiveSlide();

    foreach ($xSlideSet['slides'] as $i => $slideData) {
        echo "Processing slide " . ($i + 1) . "...\n";

        $slide = ($i === 0) ? $firstSlide : $ppt->createSlide();

        // Title
        $titleShape = $slide->createRichTextShape();
        $titleShape->setHeight(60)->setWidth(920)->setOffsetX(30)->setOffsetY(20);
        $titleShape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $titleRun = $titleShape->createTextRun($slideData['title'] ?? 'No Title');
        $titleRun->getFont()->setBold(true)->setSize(28)->setColor(new Color('FF111111'));

        // Check for image
        $imagePath = $slideData['image_path'] ?? null;
        // Fix mixed slashes
        if ($imagePath) {
            $imagePath = str_replace('\\/', DIRECTORY_SEPARATOR, $imagePath);
            $imagePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $imagePath);
        }
        $hasImage = $imagePath && file_exists($imagePath);

        echo "  Image path: " . ($imagePath ?: 'NONE') . "\n";
        echo "  Image exists: " . ($hasImage ? 'YES' : 'NO') . "\n";

        // Content
        $bodyWidth = $hasImage ? 450 : 900;
        $bodyShape = $slide->createRichTextShape();
        $bodyShape->setHeight(420)->setWidth($bodyWidth)->setOffsetX(50)->setOffsetY(110);

        $content = $slideData['content'] ?? [];
        foreach ($content as $idx => $line) {
            $p = $idx === 0 ? $bodyShape->getActiveParagraph() : $bodyShape->createParagraph();
            $p->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);
            $p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $run = $p->createTextRun((string)$line);
            $run->getFont()->setSize(18)->setColor(new Color('FF111111'));
        }

        // Add image
        if ($hasImage) {
            try {
                echo "  Adding image...\n";
                $imageShape = $slide->createDrawingShape();
                $imageShape->setPath($imagePath);
                $imageShape->setWidth(450)->setHeight(340);
                $imageShape->setOffsetX(520)->setOffsetY(110);
                $imageShape->setName('Slide Image');
                echo "  ✓ Image added successfully\n";
            } catch (\Exception $e) {
                echo "  ✗ Image failed: " . $e->getMessage() . "\n";
            }
        }

        // Notes
        $summary = $slideData['summary'] ?? '';
        if (trim($summary) !== '') {
            $note = $slide->getNote();
            $notesShape = $note->createRichTextShape();
            $notesShape->setHeight(600)->setWidth(900)->setOffsetX(30)->setOffsetY(30);
            $notesShape->createTextRun($summary)->getFont()->setSize(14)->setColor(new Color('FF111111'));
        }

        echo "  ✓ Slide completed\n\n";
    }

    // Save
    $outputPath = __DIR__ . '/test_output/x_recreated_' . time() . '.pptx';
    echo "Saving to: $outputPath\n";

    $writer = IOFactory::createWriter($ppt, 'PowerPoint2007');
    $writer->save($outputPath);

    echo "\n✅ SUCCESS! File created: " . filesize($outputPath) . " bytes\n";
    echo "\nTrying to open in PowerPoint...\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
