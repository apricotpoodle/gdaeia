<?php
declare(strict_types=1);

namespace App\Test\TestCase\View;

use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use DOMDocument;
use DOMXPath;

class FlashContainerTest extends TestCase
{
    public function testDeuxErreursSontEmpileesDansLeMemeConteneur(): void
    {
        $request = new ServerRequest(['base' => '', 'url' => '', 'webroot' => '/']);
        $request->getFlash()->error('Erreur initiale');
        $request->getFlash()->error('Erreur suivante');

        $view = new View($request);
        $document = new DOMDocument();
        $this->assertTrue($document->loadHTML($view->element('flash/container')));
        $xpath = new DOMXPath($document);

        $containers = $xpath->query('//div[@id="flash-container"]');
        $this->assertCount(1, $containers);
        $this->assertSame('flash-container', $containers[0]->getAttribute('class'));
        $toasts = $xpath->query(
            '//div[@id="flash-container"]/div[contains(concat(" ", normalize-space(@class), " "), " toast ")]',
        );
        $this->assertCount(2, $toasts);
        $this->assertStringContainsString('Erreur initiale', $toasts[0]->textContent);
        $this->assertStringContainsString('Erreur suivante', $toasts[1]->textContent);
        $this->assertSame('danger', $toasts[0]->getAttribute('data-flash-type'));
        $this->assertFalse($toasts[0]->hasAttribute('data-bs-delay'));
    }

    public function testTousLesTypesUtilisentUnToastEtLesMessagesSontEchappes(): void
    {
        $request = new ServerRequest(['base' => '', 'url' => '', 'webroot' => '/']);
        $request->getFlash()->set('Message général');
        $request->getFlash()->success('Opération réussie');
        $request->getFlash()->error('<script>Erreur</script>');
        $request->getFlash()->warning('Attention');
        $request->getFlash()->info('Information');

        $view = new View($request);
        $document = new DOMDocument();
        $this->assertTrue($document->loadHTML($view->element('flash/container')));
        $xpath = new DOMXPath($document);
        $toasts = $xpath->query(
            '//div[@id="flash-container"]/div[contains(concat(" ", normalize-space(@class), " "), " flash-toast ")]',
        );

        $this->assertCount(5, $toasts);
        $this->assertSame(
            ['success', 'success', 'danger', 'warning', 'info'],
            array_map(static fn($toast) => $toast->getAttribute('data-flash-type'), iterator_to_array($toasts)),
        );
        $this->assertCount(0, $xpath->query('//div[@id="flash-container"]//script'));
        $this->assertStringContainsString('<script>Erreur</script>', $toasts[2]->textContent);
        $this->assertSame('assertive', $toasts[3]->getAttribute('aria-live'));
        $this->assertSame('polite', $toasts[4]->getAttribute('aria-live'));
    }
}
