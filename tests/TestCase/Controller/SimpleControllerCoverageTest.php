<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ValidationsequencesController;
use App\Controller\WorkflowSettingsController;
use Authorization\IdentityInterface;
use Authorization\Policy\Result;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/** Vérifie les actions d’index des contrôleurs d’administration simples. */
class SimpleControllerCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('Validationsequences');
        TableRegistry::getTableLocator()->remove('WorkflowSettings');
        parent::tearDown();
    }

    public function testLIndexDesSequencesAutoriseLaRessource(): void
    {
        $table = $this->createMock(Table::class);
        $table->expects($this->once())->method('newEmptyEntity')->willReturn(new Entity());
        TableRegistry::getTableLocator()->set('Validationsequences', $table);

        $controller = new ValidationsequencesController($this->requestWithAllowedIdentity());
        $controller->index();

        $this->assertTrue(true);
    }

    public function testLIndexDuParametrageAutoriseLaRessource(): void
    {
        $table = $this->createMock(Table::class);
        $table->expects($this->once())->method('newEmptyEntity')->willReturn(new Entity());
        TableRegistry::getTableLocator()->set('WorkflowSettings', $table);

        $controller = new WorkflowSettingsController($this->requestWithAllowedIdentity());
        $controller->index();

        $this->assertTrue(true);
    }

    private function requestWithAllowedIdentity(): ServerRequest
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('can')->willReturn(true);
        $identity->method('canResult')->willReturn(new Result(true));

        return (new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']))
            ->withAttribute('identity', $identity);
    }
}
