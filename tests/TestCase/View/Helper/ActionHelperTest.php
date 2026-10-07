<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Helper;

use App\View\Action\UiAction;
use App\View\Helper\ActionHelper;
use Authorization\IdentityInterface;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use InvalidArgumentException;
use RuntimeException;

/** Vérifie les trois rendus HTML et le refus d’une action non autorisée. */
class ActionHelperTest extends TestCase
{
    public function testRendUnLienAvecIconeEtConfirmation(): void
    {
        $helper = $this->helper();
        $action = new UiAction(
            UiAction::TYPE_LINK,
            '<Commande>',
            'fa-check',
            '/pages/home',
            'home',
            null,
            ['confirm' => 'Confirmer ?'],
            false,
        );

        $html = $helper->render($action);

        $this->assertStringContainsString('fa-check', $html);
        $this->assertStringContainsString('return confirm("Confirmer ?");', $html);
        $this->assertStringContainsString('&lt;Commande&gt;', $html);
    }

    public function testRendUnPostLinkSansIcone(): void
    {
        $html = $this->helper()->render(new UiAction(
            UiAction::TYPE_POST_LINK,
            'Supprimer',
            null,
            '/references/delete/12',
            'delete',
            null,
            [],
            false,
        ));

        $this->assertStringContainsString('requestSubmit()', $html);
        $this->assertStringContainsString('Supprimer', $html);
    }

    public function testRendUnBouton(): void
    {
        $html = $this->helper()->render(new UiAction(
            UiAction::TYPE_BUTTON,
            'Dupliquer',
            'fa-copy',
            '#',
            'duplicate',
            null,
            ['id' => 'duplicate'],
            false,
        ));

        $this->assertStringContainsString('<button', $html);
        $this->assertStringContainsString('id="duplicate"', $html);
    }

    public function testRetourneUneChaineVideSiLActionEstInterdite(): void
    {
        $action = new UiAction(UiAction::TYPE_LINK, 'Protégée', null, '#', 'view', null);

        $this->assertSame('', $this->helper()->render($action));
    }

    public function testRefuseUnTypeInconnu(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->helper()->render(new UiAction('unknown', 'Invalide', null, '#', 'view', null, [], false));
    }

    public function testIsAllowedRetourneFauxSiLeServiceDePolicyEchoue(): void
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('can')->willThrowException(new RuntimeException('Policy indisponible.'));
        $request = (new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']))
            ->withAttribute('identity', $identity);
        $view = new View($request);
        /** @var \App\View\Helper\ActionHelper $helper */
        $helper = $view->loadHelper('Action', ['className' => ActionHelper::class]);

        $this->assertFalse($helper->isAllowed(new UiAction(UiAction::TYPE_LINK, 'Protégée', null, '#', 'view', null)));
    }

    public function testIsAllowedRetourneVraiSiLaPolicyAutoriseLAction(): void
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('can')->willReturn(true);
        $request = (new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']))
            ->withAttribute('identity', $identity);
        $view = new View($request);
        /** @var \App\View\Helper\ActionHelper $helper */
        $helper = $view->loadHelper('Action', ['className' => ActionHelper::class]);

        $this->assertTrue($helper->isAllowed(new UiAction(UiAction::TYPE_LINK, 'Autorisée', null, '#', 'view', null)));
    }

    private function helper(): ActionHelper
    {
        $view = new View(new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']));

        /** @var \App\View\Helper\ActionHelper $helper */
        $helper = $view->loadHelper('Action', ['className' => ActionHelper::class]);

        return $helper;
    }
}
