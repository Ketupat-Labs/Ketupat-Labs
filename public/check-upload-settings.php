<?php
/**
 * Diagnostic page to check PHP upload settings
 * Access at: http://127.0.0.1:8000/check-upload-settings.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Upload Settings Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .setting { margin: 15px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #007bff; }
        .setting-name { font-weight: bold; color: #555; }
        .setting-value { color: #333; font-size: 18px; margin-top: 5px; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .info { margin: 20px 0; padding: 15px; background: #d1ecf1; border-left: 4px solid #17a2b8; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 PHP Upload Settings Diagnostic</h1>
        
        <?php
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $maxFileUploads = ini_get('max_file_uploads');
        $phpIniLoaded = php_ini_loaded_file();
        $phpIniScanned = php_ini_scanned_files();
        
        // Convert to bytes for comparison
        function convertToBytes($val) {
            $val = trim($val);
            $last = strtolower($val[strlen($val)-1]);
            $val = (int)$val;
            switch($last) {
                case 'g': $val *= 1024;
                case 'm': $val *= 1024;
                case 'k': $val *= 1024;
            }
            return $val;
        }
        
        $uploadMaxBytes = convertToBytes($uploadMaxFilesize);
        $postMaxBytes = convertToBytes($postMaxSize);
        $requiredBytes = 50 * 1024 * 1024; // 50MB
        
        $uploadOk = $uploadMaxBytes >= $requiredBytes;
        $postOk = $postMaxBytes >= ($requiredBytes + 2 * 1024 * 1024); // 52MB
        ?>
        
        <div class="info">
            <strong>📁 Loaded Configuration File:</strong><br>
            <code><?php echo htmlspecialchars($phpIniLoaded ?: 'None'); ?></code>
            <?php if ($phpIniScanned): ?>
                <br><br><strong>📁 Scanned Additional .ini files:</strong><br>
                <code><?php echo htmlspecialchars($phpIniScanned); ?></code>
            <?php endif; ?>
        </div>
        
        <div class="setting <?php echo $uploadOk ? 'success' : 'error'; ?>">
            <div class="setting-name">upload_max_filesize</div>
            <div class="setting-value"><?php echo htmlspecialchars($uploadMaxFilesize); ?> 
                (<?php echo number_format($uploadMaxBytes / 1024 / 1024, 2); ?> MB)
            </div>
            <?php if (!$uploadOk): ?>
                <div style="margin-top: 10px; color: #721c24;">
                    ❌ <strong>Too small!</strong> Need at least 50M (currently <?php echo number_format($uploadMaxBytes / 1024 / 1024, 2); ?> MB)
                </div>
            <?php else: ?>
                <div style="margin-top: 10px; color: #155724;">
                    ✅ OK - Sufficient for 50MB uploads
                </div>
            <?php endif; ?>
        </div>
        
        <div class="setting <?php echo $postOk ? 'success' : 'warning'; ?>">
            <div class="setting-name">post_max_size</div>
            <div class="setting-value"><?php echo htmlspecialchars($postMaxSize); ?> 
                (<?php echo number_format($postMaxBytes / 1024 / 1024, 2); ?> MB)
            </div>
            <?php if (!$postOk): ?>
                <div style="margin-top: 10px; color: #856404;">
                    ⚠️ <strong>Should be larger!</strong> Recommended: 52M (currently <?php echo number_format($postMaxBytes / 1024 / 1024, 2); ?> MB)
                    <br><small>post_max_size should be slightly larger than upload_max_filesize</small>
                </div>
            <?php else: ?>
                <div style="margin-top: 10px; color: #155724;">
                    ✅ OK - Sufficient for 50MB uploads
                </div>
            <?php endif; ?>
        </div>
        
        <div class="setting">
            <div class="setting-name">max_file_uploads</div>
            <div class="setting-value"><?php echo htmlspecialchars($maxFileUploads); ?></div>
        </div>
        
        <div class="info" style="margin-top: 30px;">
            <h3>📝 How to Fix:</h3>
            <ol>
                <li>Open the php.ini file shown above: <code><?php echo htmlspecialchars($phpIniLoaded ?: 'C:\\xampp\\php\\php.ini'); ?></code></li>
                <li>Find these lines (use Ctrl+F to search):<br>
                    <code>upload_max_filesize</code><br>
                    <code>post_max_size</code>
                </li>
                <li>Change them to:<br>
                    <code>upload_max_filesize = 50M</code><br>
                    <code>post_max_size = 52M</code>
                </li>
                <li><strong>IMPORTANT:</strong> Restart Apache in XAMPP Control Panel</li>
                <li>Refresh this page to verify the changes</li>
            </ol>
            
            <h3>🚀 Quick Fix Script:</h3>
            <p>Run this PowerShell command in your project directory:</p>
            <pre style="background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto;">.\update-php-upload-limit.ps1</pre>
            <p><small>Then restart Apache!</small></p>
        </div>
        
        <div class="info" style="margin-top: 20px;">
            <h3>⚠️ Note:</h3>
            <p>If you're using Laravel's built-in server (<code>php artisan serve</code>), it uses the CLI php.ini, not Apache's php.ini.</p>
            <p>For XAMPP Apache, make sure you edit <code>C:\xampp\php\php.ini</code> and restart Apache.</p>
        </div>
    </div>
</body>
</html>

