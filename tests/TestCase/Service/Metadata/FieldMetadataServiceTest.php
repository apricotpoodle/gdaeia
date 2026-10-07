<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Metadata;

use App\Model\Entity\FieldDefinition;
use App\Service\Metadata\FieldMetadataService;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/** Vérifie le chargement, le tri et la mise en cache des métadonnées de champs. */
class FieldMetadataServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('FieldDefinitions');
        parent::tearDown();
    }

    public function testChargeLesDefinitionsEtLesMetEnCache(): void
    {
        $query = $this->createMock(SelectQuery::class);
        $query->expects($this->once())->method('select')->with([
            FieldDefinition::FIELD_FIELD,
            FieldDefinition::FIELD_LABEL,
            FieldDefinition::FIELD_DESCRIPTION,
        ])->willReturnSelf();
        $query->expects($this->once())->method('where')->with([
            FieldDefinition::FIELD_RESOURCE => 'Applicationforms',
            FieldDefinition::FIELD_ACTIVE => true,
        ])->willReturnSelf();
        $query->expects($this->once())->method('orderByAsc')
            ->with(FieldDefinition::FIELD_POSITION)
            ->willReturnSelf();
        $query->expects($this->once())->method('all')->willReturn(new ResultSet([
            new FieldDefinition([
                'field' => 'jobtitle',
                'label' => 'Intitulé du poste',
                'description' => 'Libellé de la demande',
            ]),
            new FieldDefinition([
                'field' => 'cgr',
                'label' => 'CGR',
                'description' => null,
            ]),
        ]));
        $table = $this->createMock(Table::class);
        $table->expects($this->once())->method('find')->willReturn($query);
        TableRegistry::getTableLocator()->set('FieldDefinitions', $table);

        $service = new FieldMetadataService();

        $this->assertSame('Intitulé du poste', $service->label('Applicationforms', 'jobtitle'));
        $this->assertSame('Libellé de la demande', $service->description('Applicationforms', 'jobtitle'));
        $this->assertNull($service->description('Applicationforms', 'cgr'));
        $this->assertSame('Intitulé du poste', $service->label('Applicationforms', 'jobtitle'));
        $this->assertSame([
            'jobtitle' => ['label' => 'Intitulé du poste', 'description' => 'Libellé de la demande'],
            'cgr' => ['label' => 'CGR', 'description' => null],
        ], $service->all('Applicationforms'));
        $this->assertSame('unknown', $service->label('Applicationforms', 'unknown'));
    }
}
