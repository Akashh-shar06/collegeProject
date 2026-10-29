<?php
/**
 * Deep Button & Modal Auditor
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

$viewsDir = __DIR__ . '/../app/Views';
$allFiles = array_merge(
    glob($viewsDir . '/*.php'),
    glob($viewsDir . '/*/*.php'),
    glob($viewsDir . '/*/*/*.php')
);
$appJs = file_get_contents(__DIR__ . '/../public/assets/js/app.js');

// Collect all elements with id="..." across all views
$allIds = [];
$allJsContent = $appJs;

foreach ($allFiles as $file) {
    $content = file_get_contents($file);
    $allJsContent .= "\n" . $content;
    preg_match_all('/id=["\']([^"\']+)["\']/i', $content, $m);
    if (!empty($m[1])) {
        foreach ($m[1] as $id) {
            $allIds[$id] = true;
        }
    }
}

// Find all document.getElementById('X') calls in onclick or script
$modalCheckIssues = [];
preg_match_all('/document\.getElementById\(["\']([^"\']+)["\']\)/i', $allJsContent, $mGetId);
if (!empty($mGetId[1])) {
    foreach ($mGetId[1] as $targetId) {
        // Exclude PHP echo variables if dynamic
        if (strpos($targetId, '<?') !== false) continue;
        if (!isset($allIds[$targetId])) {
            $modalCheckIssues[] = "Target ID '{$targetId}' in document.getElementById is NOT found in any view!";
        }
    }
}

// Find all onclick functions and check if defined in JS or <script> tags
$definedFunctions = [];
preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/i', $allJsContent, $mFn);
if (!empty($mFn[1])) {
    foreach ($mFn[1] as $fn) {
        $definedFunctions[$fn] = true;
    }
}

$missingFunctions = [];
preg_match_all('/onclick=["\']([^"\']+)["\']/i', $allJsContent, $mOc);
if (!empty($mOc[1])) {
    foreach ($mOc[1] as $oc) {
        // find function names
        preg_match_all('/\b([a-zA-Z0-9_]+)\s*\(/', $oc, $mCalls);
        if (!empty($mCalls[1])) {
            foreach ($mCalls[1] as $calledFn) {
                $builtIns = ['alert', 'confirm', 'prompt', 'history', 'location', 'console', 'window', 'event', 'document', 'encodeURIComponent', 'decodeURIComponent', 'parseInt', 'parseFloat', 'closeTimetableAssignModal', 'openTimetableAssignModal'];
                if (!in_array($calledFn, $builtIns) && !isset($definedFunctions[$calledFn])) {
                    $missingFunctions[$calledFn] = $oc;
                }
            }
        }
    }
}

echo "--- DEEP BUTTON AUDIT REPORT ---\n";
echo "Missing Modal/Target IDs (" . count($modalCheckIssues) . "):\n";
print_r(array_unique($modalCheckIssues));

echo "\nMissing JS Functions in onclick (" . count($missingFunctions) . "):\n";
print_r($missingFunctions);
