<?php
/**
 * Auto-fix missing type="button" on non-submit buttons with onclick/data attributes
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

$viewsDir = __DIR__ . '/../app/Views';
$allFiles = array_merge(
    glob($viewsDir . '/*.php'),
    glob($viewsDir . '/*/*.php'),
    glob($viewsDir . '/*/*/*.php')
);

$fixedCount = 0;

foreach ($allFiles as $file) {
    $content = file_get_contents($file);

    // Pattern to match <button ...> where type is NOT specified, but onclick or data- attribute is present
    $newContent = preg_replace_callback('/<button\b((?:(?!type=)[^>])*)>/i', function($m) {
        $attrs = $m[1];
        // If it has onclick or data- or class with tab/btn that isn't explicitly submit
        if (preg_match('/(onclick=|data-|tab-btn|classList|closeModal)/i', $attrs)) {
            return '<button type="button"' . $attrs . '>';
        }
        return $m[0];
    }, $content);

    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        $fixedCount++;
        echo "Fixed button types in: " . str_replace(realpath(__DIR__ . '/..') . '/', '', realpath($file)) . "\n";
    }
}

echo "Total files updated with explicit type=\"button\": {$fixedCount}\n";
