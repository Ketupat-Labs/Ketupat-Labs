<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Semula Kata Laluan</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding: 40px 20px 20px 20px;">
                            <img src="{{ $logoUrl }}" alt="CompuPlay Logo" style="max-width: 200px; height: auto; margin-bottom: 30px; display: block; margin-left: auto; margin-right: auto;" />
                        </td>
                    </tr>
                    
                    <!-- Heading -->
                    <tr>
                        <td style="padding: 0 40px 20px 40px;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #333333; text-align: center;">
                                Set Semula Kata Laluan
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content Paragraph -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0; font-size: 16px; line-height: 1.6; color: #666666; text-align: center;">
                                Anda menerima e-mel ini kerana kami menerima permintaan set semula kata laluan untuk akaun anda.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Reset Button -->
                    <tr>
                        <td align="center" style="padding: 0 40px 30px 40px;">
                            <a href="{{ $url }}" style="display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #4a90e2, #357abd); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">
                                Set Semula Kata Laluan
                            </a>
                        </td>
                    </tr>
                    
                    <!-- Expiry Notice -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #999999; text-align: center;">
                                Pautan set semula kata laluan ini akan tamat tempoh dalam {{ $expireMinutes }} minit.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Security Notice -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #999999; text-align: center;">
                                Jika anda tidak meminta set semula kata laluan, tiada tindakan lanjut diperlukan.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Alternative Link -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0; font-size: 12px; line-height: 1.6; color: #999999; text-align: center;">
                                Jika butang di atas tidak berfungsi, salin dan tampal pautan berikut ke pelayar web anda:
                            </p>
                            <p style="margin: 10px 0 0 0; font-size: 12px; line-height: 1.6; color: #4a90e2; text-align: center; word-break: break-all;">
                                {{ $url }}
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #f8f9fa; border-top: 1px solid #e0e0e0; border-radius: 0 0 8px 8px;">
                            <p style="margin: 0; font-size: 12px; line-height: 1.6; color: #999999; text-align: center;">
                                © {{ date('Y') }} CompuPlay. Hak cipta terpelihara.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

