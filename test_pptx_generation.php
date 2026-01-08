<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Bullet;

echo "=== TESTING PPTX GENERATION ===\n\n";

try {
    // Create a simple presentation
    echo "1. Creating PhpPresentation object...\n";
    $ppt = new PhpPresentation();

    echo "2. Getting active slide...\n";
    $slide = $ppt->getActiveSlide();

    echo "3. Adding title...\n";
    $titleShape = $slide->createRichTextShape();
    $titleShape->setHeight(60)->setWidth(920)->setOffsetX(30)->setOffsetY(20);
    $titleShape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $titleRun = $titleShape->createTextRun('Test Slide Title');
    $titleRun->getFont()->setBold(true)->setSize(28)->setColor(new Color('FF111111'));

    echo "4. Adding content...\n";
    $bodyShape = $slide->createRichTextShape();
    $bodyShape->setHeight(420)->setWidth(900)->setOffsetX(50)->setOffsetY(110);

    $points = [
        'First bullet point',
        'Second bullet point',
        'Third bullet point'
    ];

    foreach ($points as $idx => $point) {
        $p = $idx === 0 ? $bodyShape->getActiveParagraph() : $bodyShape->createParagraph();
        $p->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);
        $p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $run = $p->createTextRun($point);
        $run->getFont()->setSize(18)->setColor(new Color('FF111111'));
    }

    echo "5. Creating output directory...\n";
    $tmpPath = __DIR__ . '/test_output';
    if (!is_dir($tmpPath)) {
        mkdir($tmpPath, 0775, true);
    }

    $filePath = $tmpPath . '/test_presentation_' . time() . '.pptx';

    echo "6. Creating PowerPoint2007 writer...\n";
    $writer = IOFactory::createWriter($ppt, 'PowerPoint2007');

    echo "7. Saving file to: $filePath\n";
    $writer->save($filePath);

    echo "\n✅ SUCCESS!\n";
    echo "File created: $filePath\n";
    echo "File size: " . filesize($filePath) . " bytes\n";

    // Check if file is readable
    if (file_exists($filePath) && is_readable($filePath)) {
        echo "✓ File exists and is readable\n";

        // Try to read it back
        echo "\n8. Testing if file can be read back...\n";
        $reader = IOFactory::createReader('PowerPoint2007');
        $loadedPpt = $reader->load($filePath);
        echo "✓ File successfully loaded back\n";
        echo "   Slides count: " . $loadedPpt->getSlideCount() . "\n";

    } else {
        echo "✗ File not accessible\n";
    }

} catch (\Exception $e) {
    echo "\n❌ ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
