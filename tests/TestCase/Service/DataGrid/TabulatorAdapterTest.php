<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\DataGrid;

use App\Service\DataGrid\TabulatorAdapter;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;

class TabulatorAdapterTest extends TestCase
{
    public function testLaReponseRespecteLeFormatTabulatorEtAppliqueLesDroits(): void
    {
        $first = new Entity(['id' => 10]);
        $second = new Entity(['id' => 20]);
        $page = $this->createStub(PaginatedInterface::class);
        $page->method('items')->willReturn([$first, $second]);
        $page->method('pagingParams')->willReturn(['pageCount' => 3]);

        $result = (new TabulatorAdapter())->adaptResponse(
            $page,
            static fn(Entity $entity): array => ['actions' => ['edit' => $entity->id === 10]],
        );

        $this->assertSame(3, $result['last_page']);
        $this->assertCount(2, $result['data']);
        $this->assertSame(['actions' => ['edit' => true]], $result['data'][0]->grid_rights);
        $this->assertSame(['actions' => ['edit' => false]], $result['data'][1]->grid_rights);
    }

    public function testLaReponseUtiliseUnePageParDefautSansFormateurDeDroits(): void
    {
        $entity = new Entity(['id' => 10]);
        $page = $this->createStub(PaginatedInterface::class);
        $page->method('items')->willReturn([$entity]);
        $page->method('pagingParams')->willReturn([]);

        $result = (new TabulatorAdapter())->adaptResponse($page);

        $this->assertSame(1, $result['last_page']);
        $this->assertFalse($result['data'][0]->has('grid_rights'));
    }
}
