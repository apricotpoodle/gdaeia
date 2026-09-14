<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\DataGrid;

use App\Service\DataGrid\TabulatorAdapter;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;

class TabulatorAdapterTest extends TestCase
{
    public function testLaRequeteTraduitLeTriEtLesFiltresSimples(): void
    {
        $query = $this->queryForAlias('Users');
        $whereCalls = [];
        $query->expects($this->once())->method('orderBy')->with(['Users.id' => 'DESC'])->willReturnSelf();
        $query->expects($this->exactly(2))->method('where')
            ->willReturnCallback(function (array $conditions) use (&$whereCalls, $query): SelectQuery {
                $whereCalls[] = $conditions;

                return $query;
            });
        $request = new ServerRequest([
            'query' => [
                'sorters' => [['field' => 'id', 'dir' => 'desc']],
                'filters' => [
                    ['field' => 'id', 'type' => 'like', 'value' => '12'],
                    ['field' => 'role.name', 'type' => 'like', 'value' => 'administrateur'],
                ],
            ],
        ]);

        $this->assertSame($query, (new TabulatorAdapter())->adaptRequest($request, $query));
        $this->assertSame([
            ['Users.id' => 12],
            ['Roles.name LIKE' => '%administrateur%'],
        ], $whereCalls);
    }

    public function testLaRequeteTraduitUneBorneInferieureDePlageDeDates(): void
    {
        $query = $this->queryForAlias('Users');
        $query->expects($this->once())->method('where')
            ->with(['Users.created >=' => '2026-01-15 00:00:00'])
            ->willReturnSelf();
        $request = new ServerRequest([
            'query' => ['filters' => [[
                'field' => 'created',
                'value' => ['start' => '2026-01-15'],
            ]]],
        ]);

        (new TabulatorAdapter())->adaptRequest($request, $query);
    }

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

    private function queryForAlias(string $alias): SelectQuery
    {
        $repository = $this->createStub(Table::class);
        $repository->method('getAlias')->willReturn($alias);
        $query = $this->createMock(SelectQuery::class);
        $query->method('getRepository')->willReturn($repository);

        return $query;
    }
}
