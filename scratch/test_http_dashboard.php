<?php
// Test login & dashboard access over HTTP port 8080
$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// 1. Fetch CSRF token from login page
$ch = curl_init('http://localhost:8080/collegeProject/index.php?page=login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$loginPage = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage, $m);
$csrfToken = $m[1] ?? '';
echo "CSRF Token: " . ($csrfToken ? 'Found' : 'Missing') . "\n";

// 2. Post login
$ch = curl_init('http://localhost:8080/collegeProject/index.php?page=login_process');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $csrfToken,
    'username' => 'dsjc',
    'password' => 'Admin@123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$dashboardHtml = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: {$httpCode}\n";
echo "Dashboard HTML Length: " . strlen($dashboardHtml) . "\n";

$checks = [
    'Academic Hierarchy Card' => strpos($dashboardHtml, 'Institutional Academic Structure & Student Hierarchy') !== false,
    'FY 36 Student Pill Button' => strpos($dashboardHtml, 'FY:</span> <span>36</span>') !== false,
    'openCourseRoster Handler' => strpos($dashboardHtml, 'openCourseRoster(') !== false,
    'Student Roster Modal' => strpos($dashboardHtml, 'id="modalStudentRoster"') !== false,
    'Hierarchy JSON Payload' => strpos($dashboardHtml, 'id="academicHierarchyJson"') !== false,
    'Commerce Department' => strpos($dashboardHtml, 'Commerce Department') !== false,
    'B.Com FY Button' => strpos($dashboardHtml, 'FY:</span> <span>3</span>') !== false,
];

foreach ($checks as $k => $v) {
    echo ($v ? "  [OK] " : "  [FAIL] ") . $k . "\n";
}
