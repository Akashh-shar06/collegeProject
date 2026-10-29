<?php
declare(strict_types=1);

$baseUrl = 'http://localhost:8080/collegeProject/public/index.php';
$cookieFile = __DIR__ . '/http_leave_cookie_jar.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

function httpReq(string $url, string $method = 'GET', array $postData = [], string $cookieFile = ''): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $response ?: '',
        'url' => $effectiveUrl,
    ];
}

echo "=== 1. LOGIN AS DR_SOAP (TEACHER) ===\n";
$loginPage = httpReq($baseUrl . '?page=login', 'GET', [], $cookieFile);
preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/i', $loginPage['body'], $m);
$csrfToken = $m[1] ?? '';

$loginPost = httpReq($baseUrl . '?page=login_process', 'POST', [
    'csrf_token' => $csrfToken,
    'username' => 'dr_soap',
    'password' => 'Admin@123'
], $cookieFile);
echo "Logged in URL: {$loginPost['url']}\n";

echo "\n=== 2. LOAD LEAVE MANAGEMENT PAGE ===\n";
$leavePage = httpReq($baseUrl . '?page=module&name=leave_management', 'GET', [], $cookieFile);
echo "HTTP Code: {$leavePage['code']}\n";
echo "Has Leave Form: " . (stripos($leavePage['body'], 'apply_leave') !== false ? 'YES' : 'NO') . "\n";

// Find CSRF token and a valid leave_type_id
preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/i', $leavePage['body'], $mCsrf);
$leaveCsrf = $mCsrf[1] ?? $csrfToken;

preg_match('/value="([0-9]+)"[^>]*>[^<]*Casual Leave/i', $leavePage['body'], $mType);
$leaveTypeId = (int)($mType[1] ?? 1);
echo "Selected Leave Type ID: {$leaveTypeId}\n";

echo "\n=== 3. SUBMIT LEAVE APPLICATION ===\n";
$applyPost = httpReq($baseUrl . '?page=leave_apply', 'POST', [
    'csrf_token' => $leaveCsrf,
    'leave_type_id' => $leaveTypeId,
    'from_date' => date('Y-m-d', strtotime('+2 days')),
    'to_date' => date('Y-m-d', strtotime('+3 days')),
    'reason' => 'Attending national faculty computer science symposium',
    'contact_number' => '+1 555-0192'
], $cookieFile);
echo "HTTP Code: {$applyPost['code']}\n";
echo "Final URL: {$applyPost['url']}\n";

// Check response body for flash messages
echo "Has Error: " . (stripos($applyPost['body'], 'Failed to submit leave application') !== false ? 'YES' : 'NO') . "\n";
echo "Has Success: " . (stripos($applyPost['body'], 'Leave application submitted successfully') !== false ? 'YES' : 'NO') . "\n";
echo "Has Application in Table: " . (stripos($applyPost['body'], 'Attending national faculty computer science symposium') !== false ? 'YES' : 'NO') . "\n";

// Print any error banner if present
if (preg_match('/alert[^>]*>(.*?)<\/div>/is', $applyPost['body'], $mAlert)) {
    echo "Alert Text: " . strip_tags($mAlert[0]) . "\n";
}
