<?php
namespace Tests\Core;

use Tests\TestCase;
use App\Core\Router;

class DummyController {
    public static bool $executed = false;
    public function testAction(): void {
        self::$executed = true;
    }
}

class RouterTest extends TestCase {
    public function testRegisterGetRoute(): void {
        $router = new Router();
        $router->get('test_page', [DummyController::class, 'testAction']);

        $ref = new \ReflectionClass($router);
        $prop = $ref->getProperty('routes');
        $routes = $prop->getValue($router);

        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('test_page', $routes['GET']);
        $this->assertEquals([DummyController::class, 'testAction'], $routes['GET']['test_page']);
    }

    public function testRegisterPostRoute(): void {
        $router = new Router();
        $router->post('submit_form', [DummyController::class, 'testAction']);

        $ref = new \ReflectionClass($router);
        $prop = $ref->getProperty('routes');
        $routes = $prop->getValue($router);

        $this->assertArrayHasKey('POST', $routes);
        $this->assertArrayHasKey('submit_form', $routes['POST']);
    }
}
