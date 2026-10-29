<?php
require_once __DIR__ . '/../tests/bootstrap.php';
require_once __DIR__ . '/../tests/TestHarness.php';
require_once __DIR__ . '/../tests/Controllers/TeacherControllerTest.php';

use Tests\TestRunner;

$runner = new TestRunner();
$runner->runClass(\Tests\Controllers\TeacherControllerTest::class);
$runner->renderReport();
