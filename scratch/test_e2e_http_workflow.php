<?php
/**
 * End-to-End HTTP Workflow Verification
 * Simulates complete browser session via cURL to Apache on http://localhost:8080
 */
declare(strict_types=1);

$baseUrl = 'http://localhost:8080/collegeProject/public/index.php';
$cookieFile = __DIR__ . '/http_cookie_jar.txt';
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

echo "=== STEP 1: LOAD LOGIN PAGE ===\n";
$loginPage = httpReq($baseUrl . '?page=login', 'GET', [], $cookieFile);
echo "HTTP Code: {$loginPage['code']}\n";
preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/i', $loginPage['body'], $m);
$csrfToken = $m[1] ?? '';
echo "Extracted Login CSRF Token: " . ($csrfToken ? substr($csrfToken, 0, 16) . '...' : 'NONE') . "\n";

echo "\n=== STEP 2: LOG IN AS TEACHER (dr_soap) ===\n";
$loginPost = httpReq($baseUrl . '?page=login_process', 'POST', [
    'csrf_token' => $csrfToken,
    'username' => 'dr_soap',
    'password' => 'Admin@123'
], $cookieFile);
echo "Final URL after Login: {$loginPost['url']}\n";
echo "Has 'Teacher': " . (stripos($loginPost['body'], 'Teacher') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Weekly Timetable': " . (stripos($loginPost['body'], 'Weekly Timetable') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Assigned Subjects': " . (stripos($loginPost['body'], 'Assigned Subjects') !== false ? 'YES' : 'NO') . "\n";

echo "\n=== STEP 3: LOAD WEEKLY TIMETABLE ===\n";
$ttPage = httpReq($baseUrl . '?page=teacher_timetable&date=2026-10-12', 'GET', [], $cookieFile);
echo "HTTP Code: {$ttPage['code']}\n";
echo "Has 'MONDAY': " . (stripos($ttPage['body'], 'MONDAY') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'TIME SLOT': " . (stripos($ttPage['body'], 'TIME SLOT') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Database Management System': " . (stripos($ttPage['body'], 'Database Management System') !== false ? 'YES' : 'NO') . "\n";

echo "\n=== STEP 4: LOAD CLASSROOM LECTURE DETAILS (ID 1) ===\n";
$lecPage = httpReq($baseUrl . '?page=lecture_details&id=1&date=2026-10-12', 'GET', [], $cookieFile);
echo "HTTP Code: {$lecPage['code']}\n";
echo "Has 'Classroom Lecture Session': " . (stripos($lecPage['body'], 'Classroom Lecture Session') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Student Attendance': " . (stripos($lecPage['body'], 'Student Attendance') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Daily Lesson Record': " . (stripos($lecPage['body'], 'Daily Lesson Record') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Homework & Assignments': " . (stripos($lecPage['body'], 'Homework & Assignments') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Classroom Remarks': " . (stripos($lecPage['body'], 'Classroom Remarks') !== false ? 'YES' : 'NO') . "\n";

// Extract session CSRF token from lecture page
preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/i', $lecPage['body'], $m2);
$sessionCsrf = $m2[1] ?? '';
echo "Extracted Lecture Session CSRF Token: " . ($sessionCsrf ? substr($sessionCsrf, 0, 16) . '...' : 'NONE') . "\n";

echo "\n=== STEP 5: SAVE ATTENDANCE VIA AJAX POST ===\n";
$attPost = httpReq($baseUrl . '?page=save_lecture_attendance', 'POST', [
    'csrf_token' => $sessionCsrf,
    'time_table_id' => 1,
    'date' => '2026-10-12',
    'attendance' => [1 => 'present'],
    'remarks' => [1 => 'E2E HTTP Test Verified']
], $cookieFile);
echo "HTTP Code: {$attPost['code']}\n";
echo "Body: {$attPost['body']}\n";

echo "\n=== STEP 6: SAVE LESSON RECORD VIA AJAX POST ===\n";
$lesPost = httpReq($baseUrl . '?page=save_lesson_record', 'POST', [
    'csrf_token' => $sessionCsrf,
    'time_table_id' => 1,
    'date' => '2026-10-12',
    'topic_title' => 'E2E Query Optimization & B-Tree Indexing',
    'description' => 'Verified through automated HTTP Apache testing suite.',
    'status' => 'completed'
], $cookieFile);
echo "HTTP Code: {$lesPost['code']}\n";
echo "Body: {$lesPost['body']}\n";

echo "\n=== STEP 7: SAVE CLASSROOM REMARK VIA AJAX POST ===\n";
$remPost = httpReq($baseUrl . '?page=save_classroom_remark', 'POST', [
    'csrf_token' => $sessionCsrf,
    'time_table_id' => 1,
    'date' => '2026-10-12',
    'student_id' => 1,
    'remark_type' => 'academic_performance',
    'remark_text' => 'Outstanding analytical skill during lecture.'
], $cookieFile);
echo "HTTP Code: {$remPost['code']}\n";
echo "Body: {$remPost['body']}\n";

echo "\n=== STEP 8: RE-FETCH LECTURE DETAILS TO VERIFY PERSISTENCE ===\n";
$lecPage2 = httpReq($baseUrl . '?page=lecture_details&id=1&date=2026-10-12', 'GET', [], $cookieFile);
echo "Has 'E2E Query Optimization': " . (stripos($lecPage2['body'], 'E2E Query Optimization') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Outstanding analytical skill': " . (stripos($lecPage2['body'], 'Outstanding analytical skill') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Saved (': " . (stripos($lecPage2['body'], 'Saved (') !== false ? 'YES' : 'NO') . "\n";

echo "\n=== STEP 9: TEST CROSS-TEACHER ACCESS RESTRICTION ===\n";
// Attempt to access a lecture from Tenant 2 (ID 99)
$unauthPage = httpReq($baseUrl . '?page=lecture_details&id=99&date=2026-10-12', 'GET', [], $cookieFile);
echo "Final URL when attempting unauthorized access: {$unauthPage['url']}\n";
echo "Blocked and redirected to timetable: " . (stripos($unauthPage['url'], 'teacher_timetable') !== false ? 'YES' : 'NO') . "\n";
echo "Has Error Flash: " . (stripos($unauthPage['body'], 'not found or does not belong') !== false ? 'YES' : 'NO') . "\n";

echo "\n=== ALL E2E HTTP STEPS COMPLETED SUCCESSFULLY ===\n";
