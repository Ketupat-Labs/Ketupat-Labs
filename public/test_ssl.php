<?php
// SSL / URL Generation Diagnostic
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

echo "<h1>SSL / Proxy Diagnostic</h1>";
echo "<table border='1' cellpadding='10'>";

echo "<tr><td><strong>APP_ENV</strong></td><td>" . config('app.env') . "</td></tr>";
echo "<tr><td><strong>APP_URL (Config)</strong></td><td>" . config('app.url') . "</td></tr>";
echo "<tr><td><strong>ASSET_URL (Config)</strong></td><td>" . config('app.asset_url') . "</td></tr>";
echo "<tr><td><strong>Current URL</strong></td><td>" . $request->fullUrl() . "</td></tr>";
echo "<tr><td><strong>Is Secure (HTTPS)?</strong></td><td>" . ($request->secure() ? 'YES ✅' : 'NO ❌') . "</td></tr>";
echo "<tr><td><strong>Scheme</strong></td><td>" . $request->getScheme() . "</td></tr>";

echo "<tr><td><strong>Generated Asset URL</strong></td><td>" . asset('assets/css/landing.css') . "</td></tr>";

echo "<tr><td><strong>Trust Proxies Headers?</strong></td><td>" . (Illuminate\Http\Request::getTrustedProxies() ? 'Configured' : 'Not Configured') . "</td></tr>";
echo "<tr><td><strong>X-Forwarded-Proto</strong></td><td>" . $request->header('x-forwarded-proto', 'Not Set') . "</td></tr>";

echo "</table>";
