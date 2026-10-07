<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Helper;

use App\Model\Entity\FieldDefinition;
use App\View\Helper\FieldMetadataHelper;
use Cake\Http\ServerRequest;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/** Vérifie l’échappement et la délégation du helper de métadonnées. */
class FieldMetadataHelperTest extends TestCase
{
    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('FieldDefinitions');
        parent::tearDown();
    }

    public function testExposeLesLibellesDescriptionsEtLeDictionnaire(): void
    {
        $this->installDefinitions();
        $view = new View(new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']));
        /** @var FieldMetadataHelper $helper */
        $helper = $view->loadHelper('FieldMetadata', ['className' => FieldMetadataHelper::class]);

        $this->assertSame('Nom &amp; prénom', $helper->label('Users', 'name'));
        $this->assertSame('Description &lt;publique&gt;', $helper->description('Users', 'name'));
        $this->assertNull($helper->description('Users', 'empty'));
        $this->assertSame('unknown', $helper->label('Users', 'unknown'));
        $this->assertArrayHasKey('name', $helper->all('Users'));
    }

    private function installDefinitions(): void
    {
        $query = $this->createStub(SelectQuery::class);
        $query->method('select')->willReturnSelf();
        $query->method('where')->willReturnSelf();
        $query->method('orderByAsc')->willReturnSelf();
        $query->method('all')->willReturn(new ResultSet([
            new FieldDefinition([
                'field' => 'name',
                'label' => 'Nom & prénom',
                'description' => 'Description <publique>',
            ]),
            new FieldDefinition([
                'field' => 'empty',
                'label' => 'Vide',
                'description' => null,
            ]),
        ]));
        $table = $this->createStub(Table::class);
        $table->method('find')->willReturn($query);
        TableRegistry::getTableLocator()->set('FieldDefinitions', $table);
    }
}
