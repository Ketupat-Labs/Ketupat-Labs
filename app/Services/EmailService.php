<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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
     * Send OTP email using Google Apps Script Bridge
     * 
     * This avoids SMTP blocks on Render by using HTTPS.
     *
     * @param string $toEmail
     * @param string $otp
     * @return bool
     */
    public static function sendOtpEmail(string $toEmail, string $otp): bool
    {
        try {
            $scriptUrl = trim(env('GOOGLE_SCRIPT_URL', ''));

            if (empty($scriptUrl)) {
                self::$lastErrorMessage = "GOOGLE_SCRIPT_URL is not set in environment variables.";
                Log::error('EmailService: ' . self::$lastErrorMessage);
                return false;
            }

            Log::info("EmailService: Attempting to send OTP email to $toEmail via Google Script Bridge");

            $response = Http::post($scriptUrl, [
                'to' => $toEmail,
                'otp' => $otp
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'success') {
                    Log::info('EmailService: OTP email sent successfully via Google Script.');
                    return true;
                } else {
                    self::$lastErrorMessage = "Google Script Response Error: " . ($data['message'] ?? 'Unknown error');
                    Log::error('EmailService: ' . self::$lastErrorMessage);
                    return false;
                }
            } else {
                self::$lastErrorMessage = "Google Script Connection Error: " . $response->status();
                Log::error('EmailService: ' . self::$lastErrorMessage);
                return false;
            }
        } catch (\Throwable $e) {
            self::$lastErrorMessage = "EmailService Exception: " . $e->getMessage();
            Log::error('EmailService: ' . self::$lastErrorMessage);
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
