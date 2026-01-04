<?php

echo "Starting migration...\n";
$output = [];
$return_var = 0;
// 2>&1 to capture stderr
exec('php artisan migrate:fresh --seed -v 2>&1', $output, $return_var);

echo "Migration finished with status: $return_var\n";
$logContent = implode("\n", $output);
file_put_contents('migration_wrapper.log', $logContent);
echo "Output saved to migration_wrapper.log\n";
