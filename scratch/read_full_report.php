<?php
$content = file_get_contents(__DIR__ . '/full_test_report.txt');
if (substr($content, 0, 2) === "\xFF\xFE") {
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
}
$pos = strpos($content, 'CAMPUS360 ERP UNIT TEST REPORT');
if ($pos !== false) {
    echo substr($content, $pos);
} else {
    // print any FAIL or ERROR
    preg_match_all('/\[(FAIL|ERROR)\].*/', $content, $matches);
    print_r($matches[0]);
    echo substr($content, -1500);
}
