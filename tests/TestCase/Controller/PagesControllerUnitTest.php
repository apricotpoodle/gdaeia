<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\PagesController;
use Authorization\AuthorizationServiceInterface;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\Exception\MissingTemplateException;

/** Couvre directement les branches de sécurité du contrôleur de pages. */
class PagesControllerUnitTest extends TestCase
{
    private mixed $originalDebug;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDebug = Configure::read('debug');
    }

    public function testSansCheminLaPageRedirigeVersAccueil(): void
    {
        $response = $this->controller()->display();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(
            '/',
            parse_url($response->getHeaderLine('Location'), PHP_URL_PATH),
        );
    }

    public function testUnCheminDeTraversalEstInterdit(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->controller()->display('..', 'Layout', 'ajax');
    }

    public function testUnTemplateAbsentEstUneErreurEnModeDebug(): void
    {
        Configure::write('debug', true);

        $this->expectException(MissingTemplateException::class);
        $this->controller()->display('inexistant');
    }

    public function testUnTemplateAbsentEstUn404HorsModeDebug(): void
    {
        Configure::write('debug', false);

        $this->expectException(NotFoundException::class);
        $this->controller()->display('inexistant');
    }

    protected function tearDown(): void
    {
        Configure::write('debug', $this->originalDebug);
        parent::tearDown();
    }

    private function controller(): PagesController
    {
        $authorization = $this->createStub(AuthorizationServiceInterface::class);
        $authorization->method('skipAuthorization')->willReturnSelf();
        $request = (new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']))
            ->withAttribute('authorization', $authorization);

        return new PagesController($request);
    }
}
