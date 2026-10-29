<?php
declare(strict_types=1);

require_once __DIR__ . '/../tests/bootstrap.php';
require_once __DIR__ . '/../tests/TestHarness.php';

// Include Core Tests
require_once __DIR__ . '/../tests/Core/SessionTest.php';
require_once __DIR__ . '/../tests/Core/ValidatorTest.php';
require_once __DIR__ . '/../tests/Core/CSRFTest.php';
require_once __DIR__ . '/../tests/Core/PermissionTest.php';
require_once __DIR__ . '/../tests/Core/RbacIntegrityTest.php';
require_once __DIR__ . '/../tests/Core/LeaveWorkflowTest.php';
require_once __DIR__ . '/../tests/Core/HierarchyIntegrityTest.php';
require_once __DIR__ . '/../tests/Core/AuthTest.php';
require_once __DIR__ . '/../tests/Core/DatabaseTest.php';
require_once __DIR__ . '/../tests/Core/RouterTest.php';
require_once __DIR__ . '/../tests/Core/ResponseTest.php';

// Include Controller Tests
require_once __DIR__ . '/../tests/Controllers/AuthControllerTest.php';
require_once __DIR__ . '/../tests/Controllers/DashboardControllerTest.php';
require_once __DIR__ . '/../tests/Controllers/ModuleControllerTest.php';
require_once __DIR__ . '/../tests/Controllers/WorkflowControllerTest.php';
require_once __DIR__ . '/../tests/Controllers/ApiControllerTest.php';
require_once __DIR__ . '/../tests/Controllers/OnlineExamControllerTest.php';
require_once __DIR__ . '/../tests/Controllers/TimetableControllerTest.php';
require_once __DIR__ . '/../tests/Controllers/TeacherControllerTest.php';

use Tests\TestRunner;

$allTestClasses = [
    'Session' => \Tests\Core\SessionTest::class,
    'Validator' => \Tests\Core\ValidatorTest::class,
    'CSRF' => \Tests\Core\CSRFTest::class,
    'Permission' => \Tests\Core\PermissionTest::class,
    'RbacIntegrity' => \Tests\Core\RbacIntegrityTest::class,
    'LeaveWorkflow' => \Tests\Core\LeaveWorkflowTest::class,
    'HierarchyIntegrity' => \Tests\Core\HierarchyIntegrityTest::class,
    'Auth' => \Tests\Core\AuthTest::class,
    'Database' => \Tests\Core\DatabaseTest::class,
    'Router' => \Tests\Core\RouterTest::class,
    'Response' => \Tests\Core\ResponseTest::class,
    'AuthController' => \Tests\Controllers\AuthControllerTest::class,
    'DashboardController' => \Tests\Controllers\DashboardControllerTest::class,
    'ModuleController' => \Tests\Controllers\ModuleControllerTest::class,
    'WorkflowController' => \Tests\Controllers\WorkflowControllerTest::class,
    'ApiController' => \Tests\Controllers\ApiControllerTest::class,
    'OnlineExamController' => \Tests\Controllers\OnlineExamControllerTest::class,
    'TimetableController' => \Tests\Controllers\TimetableControllerTest::class,
    'TeacherController' => \Tests\Controllers\TeacherControllerTest::class,
];

$runner = new TestRunner();
foreach ($allTestClasses as $class) {
    $runner->runClass($class);
}

$success = $runner->renderReport();
exit($success ? 0 : 1);
