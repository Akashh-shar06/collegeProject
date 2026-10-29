<?php
declare(strict_types=1);

$baseUrl = 'http://localhost:8080/collegeProject/public/index.php';
$cookieFile = __DIR__ . '/http_admin_cookie_jar.txt';
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
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $response ?: '',
        'url' => $effectiveUrl,
        'error' => $error
    ];
}

echo "=== ADMIN STEP 1: LOGIN AS ADMIN ===\n";
$loginPage = httpReq($baseUrl . '?page=login', 'GET', [], $cookieFile);
preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/i', $loginPage['body'], $m);
$csrfToken = $m[1] ?? '';

$loginPost = httpReq($baseUrl . '?page=login_process', 'POST', [
    'csrf_token' => $csrfToken,
    'username' => 'admin',
    'password' => 'Admin@123'
], $cookieFile);
echo "Admin Login Final URL: {$loginPost['url']}\n";

echo "\n=== ADMIN STEP 2: LOAD TIMETABLE MANAGEMENT PAGE ===\n";
$ttAdmin = httpReq($baseUrl . '?page=module&name=time_table', 'GET', [], $cookieFile);
echo "HTTP Code: {$ttAdmin['code']}\n";
echo "Has 'Timetable Management': " . (stripos($ttAdmin['body'], 'Timetable') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Copy Schedule': " . (stripos($ttAdmin['body'], 'Copy Schedule') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Edit Timetable Entry Modal': " . (stripos($ttAdmin['body'], 'editTimetableModal') !== false ? 'YES' : 'NO') . "\n";

echo "\n=== ADMIN STEP 3: API TIMETABLE ENTRY FETCH (JSON) ===\n";
$apiJson = httpReq($baseUrl . '?page=api_timetable_entry&id=1', 'GET', [], $cookieFile);
echo "HTTP Code: {$apiJson['code']}\n";
$entryData = json_decode($apiJson['body'], true);
echo "API Success: " . (($entryData['success'] ?? false) ? 'YES' : 'NO') . " | Entry ID: " . ($entryData['entry']['id'] ?? 'null') . "\n";

echo "\n=== ADMIN STEP 4: UPDATE TIMETABLE ENTRY (ROOM UPDATE) ===\n";
preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/i', $ttAdmin['body'], $m2);
$adminCsrf = $m2[1] ?? $csrfToken;

$updatePost = httpReq($baseUrl . '?page=update_timetable', 'POST', [
    'csrf_token' => $adminCsrf,
    'id' => 1,
    'subject_name' => 'Database Management System',
    'day_of_week' => 'Monday',
    'start_time' => '07:10:00',
    'end_time' => '08:00:00',
    'room_no' => 'Block 101-A',
    'status' => 'active'
], $cookieFile);
echo "Update HTTP Code: {$updatePost['code']}\n";
echo "Redirected URL: {$updatePost['url']}\n";

echo "\n=== ADMIN STEP 5: VERIFY UPDATED ROOM IN API ===\n";
$apiJson2 = httpReq($baseUrl . '?page=api_timetable_entry&id=1', 'GET', [], $cookieFile);
$entryData2 = json_decode($apiJson2['body'], true);
echo "Updated Room No: " . ($entryData2['entry']['room_no'] ?? 'unknown') . "\n";

// Restore original room no
httpReq($baseUrl . '?page=update_timetable', 'POST', [
    'csrf_token' => $adminCsrf,
    'id' => 1,
    'subject_name' => 'Database Management System',
    'day_of_week' => 'Monday',
    'start_time' => '07:10:00',
    'end_time' => '08:00:00',
    'room_no' => 'Block 101',
    'status' => 'active'
], $cookieFile);

echo "\n=== ADMIN WORKFLOW COMPLETED SUCCESSFULLY ===\n";
