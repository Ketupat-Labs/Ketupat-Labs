<?php
/**
 * Standalone Email Test Script
 * 
 * Usage from command line on Render:
 * php test_email.php your-email@example.com
 */

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load .env if it exists (for local testing)
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

$toEmail = $argv[1] ?? getenv('MAIL_USERNAME');

if (!$toEmail) {
    die("Usage: php test_email.php <recipient-email>\n");
}

echo "Testing email sending to: $toEmail\n";

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host       = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('MAIL_USERNAME');
    $mail->Password   = getenv('MAIL_PASSWORD');
    $mail->SMTPSecure = (getenv('MAIL_ENCRYPTION') === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = getenv('MAIL_PORT') ?: 587;

    $mail->setFrom(getenv('MAIL_FROM_ADDRESS') ?: getenv('MAIL_USERNAME'), getenv('MAIL_FROM_NAME') ?: 'Ketupat Labs Test');
    $mail->addAddress($toEmail);

    $mail->isHTML(true);
    $mail->Subject = 'CompuPlay Email Test';
    $mail->Body    = "This is a test email from the standalone diagnostic script.<br><br><b>Host:</b> {$mail->Host}<br><b>Port:</b> {$mail->Port}<br><b>User:</b> {$mail->Username}";

    echo "Connecting to SMTP server...\n";
    $mail->send();
    echo "\nSUCCESS: Email has been sent!\n";
} catch (Exception $e) {
    echo "\nFAILURE: Message could not be sent.\n";
    echo "Mailer Error: {$mail->ErrorInfo}\n";
    echo "Exception: {$e->getMessage()}\n";
}
