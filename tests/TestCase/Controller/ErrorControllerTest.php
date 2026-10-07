<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ErrorController;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/** Vérifie que le contrôleur d’erreur reste accessible sans autorisation. */
class ErrorControllerTest extends TestCase
{
    public function testBeforeFilterIgnoreLautorisation(): void
    {
        $authorization = new class {
            public bool $skipped = false;

            public function skipAuthorization(): void
            {
                $this->skipped = true;
            }
        };
        $request = (new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']))
            ->withAttribute('authorization', $authorization);
        $controller = new ErrorController($request);

        $controller->beforeFilter(new Event('Controller.beforeFilter', $controller));

        $this->assertTrue($authorization->skipped);
    }

    public function testBeforeRenderUtiliseLeRepertoireDesTemplatesDErreur(): void
    {
        $controller = new ErrorController(new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']));

        $controller->beforeRender(new Event('Controller.beforeRender', $controller));

        $this->assertSame('Error', $controller->viewBuilder()->getTemplatePath());
    }
}
