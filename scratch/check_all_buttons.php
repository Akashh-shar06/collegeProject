<?php
/**
 * Comprehensive Button Auditor for Campus360 ERP
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

$viewsDir = __DIR__ . '/../app/Views';
$jsDir = __DIR__ . '/../public/assets/js';

$allFiles = array_merge(
    glob($viewsDir . '/*.php'),
    glob($viewsDir . '/*/*.php'),
    glob($viewsDir . '/*/*/*.php'),
    glob($jsDir . '/*.js')
);

$issues = [];
$buttonsFound = 0;

// Load routes from index.php
$indexContent = file_get_contents(__DIR__ . '/../public/index.php');

// Extract all JS functions defined across JS and view files
$jsFunctions = [];
foreach ($allFiles as $file) {
    $content = file_get_contents($file);
    preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/i', $content, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $fn) {
            $jsFunctions[$fn] = true;
        }
    }
}

foreach ($allFiles as $file) {
    $relPath = str_replace(realpath(__DIR__ . '/..') . '/', '', realpath($file));
    $content = file_get_contents($file);
    $lines = explode("\n", $content);

    // 1. Find all <button>, <input type="submit|button">, and <a class="...btn...">
    preg_match_all('/<(button|input|a)\b[^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE);

    foreach ($matches[0] as $match) {
        $tag = $match[0];
        $offset = $match[1];
        
        // Determine line number
        $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;

        // Is it a button or button link?
        $isButton = false;
        if (str_starts_with(strtolower($tag), '<button')) {
            $isButton = true;
        } elseif (preg_match('/type=["\'](submit|button|reset)["\']/i', $tag)) {
            $isButton = true;
        } elseif (preg_match('/class=["\'][^"\']*\bbtn\b[^"\']*["\']/i', $tag)) {
            $isButton = true;
        } elseif (preg_match('/onclick=/i', $tag)) {
            $isButton = true;
        }

        if (!$isButton) continue;

        $buttonsFound++;

        // Check onclick functions
        if (preg_match('/onclick=["\']([^"\']+)["\']/i', $tag, $ocMatch)) {
            $ocValue = trim($ocMatch[1]);
            // Extract function name if like `myFunc(...)`
            if (preg_match('/^([a-zA-Z0-9_]+)\s*\(/', $ocValue, $fnMatch)) {
                $fnName = $fnMatch[1];
                // Ignore standard JS built-ins like alert, confirm, history.back, location.reload, console.log
                $builtIns = ['alert', 'confirm', 'prompt', 'history', 'location', 'console', 'window', 'event', 'document'];
                if (!in_array($fnName, $builtIns) && !isset($jsFunctions[$fnName])) {
                    $issues[] = [
                        'file' => $relPath,
                        'line' => $lineNum,
                        'tag' => $tag,
                        'issue' => "Undefined JS function '{$fnName}' in onclick handler: {$ocValue}"
                    ];
                }
            }
        }

        // Check <button> without type inside forms
        if (str_starts_with(strtolower($tag), '<button') && !preg_match('/type=/i', $tag)) {
            $issues[] = [
                'file' => $relPath,
                'line' => $lineNum,
                'tag' => $tag,
                'issue' => "<button> missing explicit type attribute (default is submit, might cause unexpected form submission)"
            ];
        }

        // Check href="#" on button links
        if (preg_match('/href=["\']#["\']/i', $tag) && !preg_match('/onclick=|data-/i', $tag)) {
            $issues[] = [
                'file' => $relPath,
                'line' => $lineNum,
                'tag' => $tag,
                'issue' => "Button link has href='#' with no onclick or data- attribute handler"
            ];
        }

        // Check href="javascript:void(0)" or empty href without onclick/data attributes
        if (preg_match('/href=["\'](javascript:\s*void\(0\);?|javascript:;)["\']/i', $tag) && !preg_match('/onclick=|data-/i', $tag)) {
            $issues[] = [
                'file' => $relPath,
                'line' => $lineNum,
                'tag' => $tag,
                'issue' => "Button link has dummy javascript: href with no handler"
            ];
        }
    }
}

file_put_contents(__DIR__ . '/button_audit.json', json_encode([
    'total_buttons' => $buttonsFound,
    'total_issues' => count($issues),
    'issues' => $issues
], JSON_PRETTY_PRINT));

echo "Audited {$buttonsFound} buttons across " . count($allFiles) . " files. Found " . count($issues) . " potential issues.\n";
