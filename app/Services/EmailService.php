namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Store the last error message for diagnostics
     * @var string|null
     */
    public static $lastErrorMessage = null;

    /**
     * Get the last error message
     * @return string|null
     */
    public static function getLastError(): ?string
    {
        return self::$lastErrorMessage;
    }

    /**
     * Send OTP email using Gmail SMTP
     *
     * @param string $toEmail
     * @param string $otp
     * @return bool
     */
    public static function sendOtpEmail(string $toEmail, string $otp): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Check essential configuration
            $host = env('MAIL_HOST', 'smtp.gmail.com');
            $username = env('MAIL_USERNAME');
            $password = env('MAIL_PASSWORD');
            $encryption = env('MAIL_ENCRYPTION', 'tls');
            $port = env('MAIL_PORT', 587);

            if (empty($username) || empty($password)) {
                Log::error('EmailService: MAIL_USERNAME or MAIL_PASSWORD is not set in environment variables.');
                return false;
            }

            // Server settings
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';
            
            // Log connection attempt details (non-sensitive)
            Log::info("EmailService: Attempting to send OTP email to $toEmail using $host:$port ($encryption)");

            // Enable verbose debug output (optional, for troubleshooting)
            // $mail->SMTPDebug = 2; // Uncomment for debugging
            $mail->SMTPDebug = 0; // 0 = off, 1 = client, 2 = client and server

            // Recipients
            $fromAddress = env('MAIL_FROM_ADDRESS', $username);
            $fromName = env('MAIL_FROM_NAME', 'Ketupat Labs');
            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($toEmail);

            // Find logo path - try multiple methods
            $baseDir = base_path();
            $possiblePaths = [
                $baseDir . '/public/assets/images/LOGOCompuPlay.png',
                public_path('assets/images/LOGOCompuPlay.png'),
                __DIR__ . '/../../public/assets/images/LOGOCompuPlay.png',
            ];
            
            $logoPath = null;
            $logoCid = 'logo-compuplay'; // Content-ID for embedded image
            
            // Try each possible path
            foreach ($possiblePaths as $path) {
                $resolvedPath = $path ? realpath($path) : false;
                if ($resolvedPath && file_exists($resolvedPath)) {
                    $logoPath = $resolvedPath;
                    Log::info('Logo found at: ' . $logoPath);
                    break;
                }
            }
            
            // If PNG not found, try JPG
            if (!$logoPath) {
                $altPaths = [
                    $baseDir . '/public/assets/images/LogoCompuPlay.jpg',
                    public_path('assets/images/LogoCompuPlay.jpg'),
                    __DIR__ . '/../../public/assets/images/LogoCompuPlay.jpg',
                ];
                
                foreach ($altPaths as $altPath) {
                    $resolvedAltPath = $altPath ? realpath($altPath) : false;
                    if ($resolvedAltPath && file_exists($resolvedAltPath)) {
                        $logoPath = $resolvedAltPath;
                        Log::info('Alternative logo found at: ' . $logoPath);
                        break;
                    }
                }
            }
            
            // Embed logo as attachment using PHPMailer's addEmbeddedImage
            if ($logoPath && file_exists($logoPath)) {
                try {
                    $mail->addEmbeddedImage($logoPath, $logoCid, 'logo.png', 'base64', 'image/png');
                    Log::info('Logo embedded as attachment with CID: ' . $logoCid);
                } catch (\Exception $e) {
                    Log::error('Failed to embed logo: ' . $e->getMessage());
                    $logoPath = null; // Fallback to URL if embedding fails
                }
            }
            
            // Get app URL for absolute logo URL
            $appUrl = env('APP_URL', 'http://localhost:8000');
            $logoUrl = $appUrl . '/assets/images/LOGOCompuPlay.png';

            // HTML email content
            $htmlBody = self::getEmailTemplate($otp, $logoPath ? $logoCid : null, $logoUrl);
            
            // Plain text fallback
            $textBody = "Sahkan alamat emel anda\n\n";
            $textBody .= "Anda perlu mengesahkan alamat emel anda untuk terus menggunakan akaun CompuPlay anda.\n";
            $textBody .= "Masukkan kod berikut untuk mengesahkan alamat emel anda:\n\n";
            $textBody .= "{$otp}\n\n";
            $textBody .= "Kod ini akan tamat tempoh dalam 10 minit.\n\n";
            $textBody .= "Jika anda tidak meminta kod ini, sila abaikan e-mel ini.";

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Sahkan alamat emel anda - CompuPlay';
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;

            $mail->send();
            Log::info('OTP email sent successfully to: ' . $toEmail);
            return true;
        } catch (\Throwable $e) {
            $errorInfo = ($mail->ErrorInfo ?? 'No detailed error information available.');
            self::$lastErrorMessage = "PHPMailer Error: " . $e->getMessage() . " | Detail: " . $errorInfo;
            
            Log::error('EmailService Error: ' . $e->getMessage());
            Log::error('PHPMailer Detail: ' . $errorInfo);
            Log::error('Stack Trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Get HTML email template
     *
     * @param string $otp
     * @param string|null $logoCid Content-ID for embedded image (preferred method)
     * @param string $logoUrl Fallback URL if CID not available
     * @return string
     */
    private static function getEmailTemplate(string $otp, ?string $logoCid = null, string $logoUrl = ''): string
    {
        $logoImg = '';
        if (!empty($logoCid)) {
            // Use embedded image with CID (most reliable for email clients)
            $logoImg = '<img src="cid:' . htmlspecialchars($logoCid, ENT_QUOTES, 'UTF-8') . '" alt="CompuPlay Logo" style="max-width: 200px; height: auto; margin-bottom: 30px; display: block; margin-left: auto; margin-right: auto;" />';
            Log::info('Logo image HTML generated with CID: ' . $logoCid);
        } elseif (!empty($logoUrl)) {
            // Fallback to URL if CID not available
            $logoImg = '<img src="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '" alt="CompuPlay Logo" style="max-width: 200px; height: auto; margin-bottom: 30px; display: block; margin-left: auto; margin-right: auto;" />';
            Log::info('Logo image HTML generated with URL: ' . $logoUrl);
        } else {
            // If no logo found, log it
            Log::warning('No logo found for email - neither CID nor URL available');
            $logoImg = '<div style="text-align: center; padding: 20px; color: #999;">CompuPlay Logo</div>';
        }

        return '
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sahkan alamat emel anda</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding: 40px 20px 20px 20px;">
                            ' . $logoImg . '
                        </td>
                    </tr>
                    
                    <!-- Heading -->
                    <tr>
                        <td style="padding: 0 40px 20px 40px;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #333333; text-align: center;">
                                Sahkan alamat emel anda
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content Paragraph -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0; font-size: 16px; line-height: 1.6; color: #666666; text-align: center;">
                                Anda perlu mengesahkan alamat emel anda untuk terus menggunakan akaun CompuPlay anda. Masukkan kod berikut untuk mengesahkan alamat emel anda:
                            </p>
                        </td>
                    </tr>
                    
                    <!-- OTP Code Box -->
                    <tr>
                        <td align="center" style="padding: 0 40px 30px 40px;">
                            <div style="background-color: #f8f9fa; border: 2px solid #e0e0e0; border-radius: 8px; padding: 20px; display: inline-block;">
                                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #2454FF; letter-spacing: 8px; font-family: \'Courier New\', monospace;">
                                    ' . $otp . '
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Expiry Notice -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #999999; text-align: center;">
                                Kod ini akan tamat tempoh dalam 10 minit.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #f8f9fa; border-top: 1px solid #e0e0e0; border-radius: 0 0 8px 8px;">
                            <p style="margin: 0; font-size: 12px; line-height: 1.6; color: #999999; text-align: center;">
                                Jika anda tidak meminta kod ini, sila abaikan e-mel ini.
                            </p>
                            <p style="margin: 10px 0 0 0; font-size: 12px; line-height: 1.6; color: #999999; text-align: center;">
                                © ' . date('Y') . ' CompuPlay. Hak cipta terpelihara.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}
