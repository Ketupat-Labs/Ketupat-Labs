<?php
/**
 * Quick PHP settings checker
 * Shows what the web server (Laravel) is actually using
 */
header('Content-Type: text/plain');
echo "=== PHP Settings (Web Server) ===\n\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "\nphp_ini_loaded_file: " . php_ini_loaded_file() . "\n";
echo "php_ini_scanned_files: " . (php_ini_scanned_files() ?: 'None') . "\n";
echo "\n=== Converted to Bytes ===\n";
function toBytes($val) {
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
echo "upload_max_filesize: " . number_format(toBytes(ini_get('upload_max_filesize'))) . " bytes (" . number_format(toBytes(ini_get('upload_max_filesize')) / 1024 / 1024, 2) . " MB)\n";
echo "post_max_size: " . number_format(toBytes(ini_get('post_max_size'))) . " bytes (" . number_format(toBytes(ini_get('post_max_size')) / 1024 / 1024, 2) . " MB)\n";
?>

