<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\HostHeaderMiddleware;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** Vérifie la protection contre l’injection d’en-tête Host. */
class HostHeaderMiddlewareTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $originalApp;

    private mixed $originalDebug;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalApp = (array)Configure::read('App', []);
        $this->originalDebug = Configure::read('debug');
    }

    public function testLeModeDebugLaissePasserLaRequeteSansConfiguration(): void
    {
        Configure::write('debug', true);
        Configure::write('App.fullBaseUrl', null);
        $request = $this->requestWithHost('attacker.example.test');
        $response = new Response();
        $handler = $this->handlerReturning($request, $response);

        $this->assertSame($response, (new HostHeaderMiddleware())->process($request, $handler));
    }

    public function testLaProductionExigeUneUrlDeBase(): void
    {
        Configure::write('debug', false);
        Configure::write('App.fullBaseUrl', null);
        $request = $this->requestWithHost('app.example.test');

        $this->expectExceptionMessage('App.fullBaseUrl is not configured');
        (new HostHeaderMiddleware())->process($request, $this->handlerStub());
    }

    public function testRefuseUnHostDifferentDeCeluiDeLaConfiguration(): void
    {
        Configure::write('debug', false);
        Configure::write('App.fullBaseUrl', 'https://app.example.test');
        $request = $this->requestWithHost('attacker.example.test');

        $this->expectExceptionMessage('Invalid Host header');
        (new HostHeaderMiddleware())->process($request, $this->handlerStub());
    }

    public function testAccepteUnHostInsensibleALaCasse(): void
    {
        Configure::write('debug', false);
        Configure::write('App.fullBaseUrl', 'https://APP.EXAMPLE.TEST');
        $request = $this->requestWithHost('app.example.test');
        $response = new Response();

        $this->assertSame(
            $response,
            (new HostHeaderMiddleware())->process($request, $this->handlerReturning($request, $response)),
        );
    }

    public function testAccepteUneConfigurationSansHost(): void
    {
        Configure::write('debug', false);
        Configure::write('App.fullBaseUrl', '/application');
        $request = $this->requestWithHost('app.example.test');
        $response = new Response();

        $this->assertSame(
            $response,
            (new HostHeaderMiddleware())->process($request, $this->handlerReturning($request, $response)),
        );
    }

    protected function tearDown(): void
    {
        Configure::write('App', $this->originalApp);
        Configure::write('debug', $this->originalDebug);
        parent::tearDown();
    }

    private function requestWithHost(string $host): ServerRequest
    {
        return (new ServerRequest())->withUri(new Uri('https://' . $host . '/'));
    }

    private function handlerReturning(ServerRequest $request, ResponseInterface $response): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        return $handler;
    }

    private function handlerStub(): RequestHandlerInterface
    {
        return $this->createStub(RequestHandlerInterface::class);
    }
}
