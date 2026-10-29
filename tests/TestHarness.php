<?php
namespace Tests;

class TestAssertionException extends \Exception {}

abstract class TestCase {
    private int $assertionsCount = 0;
    private array $failures = [];

    public function setUp(): void {}
    public function tearDown(): void {}

    public function getAssertionsCount(): int {
        return $this->assertionsCount;
    }

    public function getFailures(): array {
        return $this->failures;
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void {
        $this->assertionsCount++;
        if ($expected !== $actual) {
            $msg = $message ?: "Failed asserting that " . var_export($actual, true) . " matches expected " . var_export($expected, true);
            throw new TestAssertionException($msg);
        }
    }

    protected function assertNotEquals(mixed $expected, mixed $actual, string $message = ''): void {
        $this->assertionsCount++;
        if ($expected === $actual) {
            $msg = $message ?: "Failed asserting that " . var_export($actual, true) . " does not match " . var_export($expected, true);
            throw new TestAssertionException($msg);
        }
    }

    protected function assertTrue(mixed $condition, string $message = ''): void {
        $this->assertionsCount++;
        if ($condition !== true) {
            $msg = $message ?: "Failed asserting that " . var_export($condition, true) . " is true";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertFalse(mixed $condition, string $message = ''): void {
        $this->assertionsCount++;
        if ($condition !== false) {
            $msg = $message ?: "Failed asserting that " . var_export($condition, true) . " is false";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertNull(mixed $value, string $message = ''): void {
        $this->assertionsCount++;
        if ($value !== null) {
            $msg = $message ?: "Failed asserting that " . var_export($value, true) . " is null";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertNotNull(mixed $value, string $message = ''): void {
        $this->assertionsCount++;
        if ($value === null) {
            $msg = $message ?: "Failed asserting that value is not null";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertArrayHasKey(string|int $key, array $array, string $message = ''): void {
        $this->assertionsCount++;
        if (!array_key_exists($key, $array)) {
            $msg = $message ?: "Failed asserting that array has key '$key'";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertContains(mixed $needle, array $haystack, string $message = ''): void {
        $this->assertionsCount++;
        if (!in_array($needle, $haystack, true)) {
            $msg = $message ?: "Failed asserting that array contains " . var_export($needle, true);
            throw new TestAssertionException($msg);
        }
    }

    protected function assertStringContainsString(string $needle, string $haystack, string $message = ''): void {
        $this->assertionsCount++;
        if (strpos($haystack, $needle) === false) {
            $msg = $message ?: "Failed asserting that '$haystack' contains '$needle'";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertCount(int $expectedCount, array|\Countable $haystack, string $message = ''): void {
        $this->assertionsCount++;
        $actualCount = count($haystack);
        if ($actualCount !== $expectedCount) {
            $msg = $message ?: "Failed asserting that count $actualCount matches expected $expectedCount";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertIsArray(mixed $value, string $message = ''): void {
        $this->assertionsCount++;
        if (!is_array($value)) {
            $msg = $message ?: "Failed asserting that " . var_export($value, true) . " is an array";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertGreaterThanOrEqual(int|float $expected, int|float $actual, string $message = ''): void {
        $this->assertionsCount++;
        if ($actual < $expected) {
            $msg = $message ?: "Failed asserting that {$actual} is greater than or equal to {$expected}";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertNotEmpty(mixed $actual, string $message = ''): void {
        $this->assertionsCount++;
        if (empty($actual)) {
            $msg = $message ?: "Failed asserting that value is not empty";
            throw new TestAssertionException($msg);
        }
    }

    protected function assertEmpty(mixed $actual, string $message = ''): void {
        $this->assertionsCount++;
        if (!empty($actual)) {
            $msg = $message ?: "Failed asserting that value is empty";
            throw new TestAssertionException($msg);
        }
    }
}

class TestRunner {
    private array $results = [];

    public function runClass(string $className): array {
        $reflection = new \ReflectionClass($className);
        $instance = new $className();

        $methods = array_filter(
            $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
            fn($m) => str_starts_with($m->getName(), 'test')
        );

        $classResults = [
            'class' => $className,
            'passed' => 0,
            'failed' => 0,
            'assertions' => 0,
            'tests' => []
        ];

        foreach ($methods as $method) {
            $methodName = $method->getName();
            $startAssertions = $instance->getAssertionsCount();
            $status = 'PASS';
            $error = null;

            try {
                $instance->setUp();
                $instance->$methodName();
                $instance->tearDown();
                $classResults['passed']++;
            } catch (TestAssertionException $e) {
                $status = 'FAIL';
                $error = $e->getMessage();
                $classResults['failed']++;
            } catch (\Throwable $e) {
                $status = 'ERROR';
                $error = $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
                $classResults['failed']++;
            }

            $assertions = $instance->getAssertionsCount() - $startAssertions;
            $classResults['assertions'] += $assertions;
            $classResults['tests'][] = [
                'name' => $methodName,
                'status' => $status,
                'error' => $error,
                'assertions' => $assertions
            ];
        }

        $this->results[] = $classResults;
        return $classResults;
    }

    public function renderReport(): bool {
        $totalPassed = 0;
        $totalFailed = 0;
        $totalAssertions = 0;
        $totalTests = 0;

        echo "\n========================================================\n";
        echo "           CAMPUS360 ERP UNIT TEST REPORT               \n";
        echo "========================================================\n\n";

        foreach ($this->results as $classRes) {
            $shortName = (new \ReflectionClass($classRes['class']))->getShortName();
            echo "Module: [{$shortName}]\n";
            echo str_repeat('-', 56) . "\n";

            foreach ($classRes['tests'] as $test) {
                $totalTests++;
                $totalAssertions += $test['assertions'];
                if ($test['status'] === 'PASS') {
                    $totalPassed++;
                    echo "  [PASS] {$test['name']} ({$test['assertions']} assertions)\n";
                } else {
                    $totalFailed++;
                    echo "  [{$test['status']}] {$test['name']}\n";
                    echo "         => Error: {$test['error']}\n";
                }
            }
            echo "\n";
        }

        echo "========================================================\n";
        echo "SUMMARY: Total Tests: {$totalTests} | Passed: {$totalPassed} | Failed: {$totalFailed} | Assertions: {$totalAssertions}\n";
        echo "========================================================\n\n";

        return $totalFailed === 0;
    }
}
