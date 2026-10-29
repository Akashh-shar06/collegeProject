<?php
$content = file_get_contents(__DIR__ . '/test_results.txt');
echo "File size: " . strlen($content) . " bytes\n";
// Let's inspect the last 1000 characters
if (substr($content, 0, 2) === "\xFF\xFE") {
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
    echo "Converted size: " . strlen($content) . " bytes\n";
}
echo "Has SUMMARY: " . (strpos($content, 'SUMMARY:') !== false ? 'YES' : 'NO') . "\n";
if (preg_match('/SUMMARY:.*?========================================================/s', $content, $m)) {
    echo $m[0] . "\n";
} else {
    // print last 500 chars
    echo substr($content, -500) . "\n";
}
