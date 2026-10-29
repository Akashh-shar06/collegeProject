<?php
/**
 * Form Action & Button Handler Checker
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

$viewsDir = __DIR__ . '/../app/Views';
$allViews = array_merge(
    glob($viewsDir . '/*.php'),
    glob($viewsDir . '/*/*.php'),
    glob($viewsDir . '/*/*/*.php')
);

$indexContent = file_get_contents(__DIR__ . '/../public/index.php');

// Extract all registered routes from index.php
preg_match_all('/\$router->(get|post)\s*\(\s*["\']([^"\']+)["\']/i', $indexContent, $mRoutes);
$routes = [];
if (!empty($mRoutes[2])) {
    foreach ($mRoutes[2] as $r) {
        $routes[$r] = true;
    }
}

$unhandledForms = [];

foreach ($allViews as $file) {
    $relPath = str_replace(realpath(__DIR__ . '/..') . '/', '', realpath($file));
    $content = file_get_contents($file);

    preg_match_all('/<form\b[^>]*action=["\']([^"\']+)["\']/i', $content, $mForms, PREG_OFFSET_CAPTURE);

    foreach ($mForms[1] as $idx => $m) {
        $actionUrl = $m[0];
        $offset = $m[1];
        $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;

        // Parse page param from index.php?page=...
        $pageParam = null;
        if (preg_match('/page=([^&"\'\s]+)/i', $actionUrl, $pm)) {
            $pageParam = $pm[1];
        } else {
            // maybe action is just a route name like "login_process" or "index.php"
            $cleanUrl = ltrim($actionUrl, './');
            if ($cleanUrl !== '' && $cleanUrl !== 'index.php') {
                $pageParam = $cleanUrl;
            }
        }

        if ($pageParam && !isset($routes[$pageParam])) {
            $unhandledForms[] = [
                'file' => $relPath,
                'line' => $lineNum,
                'action' => $actionUrl,
                'extracted_route' => $pageParam
            ];
        }
    }
}

echo "--- FORM ACTION AUDIT REPORT ---\n";
echo "Total routes in index.php: " . count($routes) . "\n";
echo "Unhandled Form Actions (" . count($unhandledForms) . "):\n";
print_r($unhandledForms);
