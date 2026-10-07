<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\DataGrid;

use App\Service\DataGrid\TabulatorAdapter;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ValueBinder;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Http\Exception\UnprocessableContentException;
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

    public function testLaRequeteUtiliseLeMappingDUnChampMetier(): void
    {
        $query = $this->queryForAlias('Applicationforms');
        $query->expects($this->once())->method('orderBy')
            ->with(['Applicationformstatuses.validationstatus_id' => 'DESC'])
            ->willReturnSelf();
        $query->expects($this->once())->method('where')
            ->with(['Applicationformstatuses.validationstatus_id' => 5])
            ->willReturnSelf();
        $request = new ServerRequest([
            'query' => [
                'sorters' => [['field' => 'validation_status', 'dir' => 'desc']],
                'filters' => [[
                    'field' => 'validation_status',
                    'type' => 'like',
                    'value' => '5',
                ]],
            ],
        ]);

        (new TabulatorAdapter())->adaptRequest($request, $query, [
            'validation_status' => 'Applicationformstatuses.validationstatus_id',
        ]);
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

    public function testLaRequeteTraduitUneBorneSuperieureDePlageDeDates(): void
    {
        $query = $this->queryForAlias('Users');
        $query->expects($this->once())->method('where')
            ->with(['Users.created <=' => '2026-01-15 23:59:59'])
            ->willReturnSelf();
        $request = new ServerRequest([
            'query' => ['filters' => [[
                'field' => 'created',
                'value' => ['end' => '2026-01-15'],
            ]]],
        ]);

        (new TabulatorAdapter())->adaptRequest($request, $query);
    }

    public function testLaRequeteTraduitUnePlageDeDatesComplete(): void
    {
        $query = $this->queryForAlias('Users');
        $sql = '';
        $query->expects($this->once())->method('where')
            ->willReturnCallback(function (callable $condition) use (&$sql, $query): SelectQuery {
                $sql = $condition(new QueryExpression())->sql(new ValueBinder());

                return $query;
            });
        $request = new ServerRequest([
            'query' => ['filters' => [[
                'field' => 'created',
                'value' => ['start' => '2026-01-01', 'end' => '2026-01-31'],
            ]]],
        ]);

        (new TabulatorAdapter())->adaptRequest($request, $query);

        $this->assertStringContainsString('Users.created BETWEEN', $sql);
    }

    public function testLaRequeteRefuseUnePlageDeDatesInversee(): void
    {
        $query = $this->stubQueryForAlias('Users');
        $request = new ServerRequest([
            'query' => ['filters' => [[
                'field' => 'created',
                'value' => ['start' => '2026-10-01', 'end' => '2026-01-01'],
            ]]],
        ]);

        $this->expectException(UnprocessableContentException::class);
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

    public function testLaRequeteTraduitTousLesOperateursEtIgnoreLesFiltresVides(): void
    {
        $query = $this->queryForAlias('Users');
        $whereCalls = [];
        $query->expects($this->never())->method('orderBy');
        $query->expects($this->exactly(8))->method('where')
            ->willReturnCallback(function (mixed $conditions) use (&$whereCalls, $query): SelectQuery {
                $whereCalls[] = $conditions;

                return $query;
            });
        $request = new ServerRequest([
            'query' => [
                'sorters' => [['field' => null, 'dir' => 'INVALID']],
                'filters' => [
                    ['field' => 'empty', 'type' => '=', 'value' => ''],
                    ['field' => 'level', 'type' => 'like', 'value' => '4'],
                    ['field' => 'name', 'type' => '=', 'value' => 'A'],
                    ['field' => 'name', 'type' => '!=', 'value' => 'B'],
                    ['field' => 'name', 'type' => '<', 'value' => 'C'],
                    ['field' => 'name', 'type' => '<=', 'value' => 'D'],
                    ['field' => 'name', 'type' => '>', 'value' => 'E'],
                    ['field' => 'name', 'type' => '>=', 'value' => 'F'],
                    ['field' => 'name', 'type' => 'unsupported', 'value' => 'G'],
                ],
            ],
        ]);

        (new TabulatorAdapter())->adaptRequest($request, $query);

        $this->assertSame([
            ['Users.level' => 4],
            ['Users.name =' => 'A'],
            ['Users.name !=' => 'B'],
            ['Users.name <' => 'C'],
            ['Users.name <=' => 'D'],
            ['Users.name >' => 'E'],
            ['Users.name >=' => 'F'],
            ['Users.name' => 'G'],
        ], $whereCalls);
    }

    public function testLaRequeteRefuseUnePlageDeDatesMalFormee(): void
    {
        $query = $this->stubQueryForAlias('Users');
        $request = new ServerRequest([
            'query' => ['filters' => [[
                'field' => 'created',
                'value' => ['start' => '15-01-2026', 'end' => '2026-01-31'],
            ]]],
        ]);

        $this->expectException(UnprocessableContentException::class);
        (new TabulatorAdapter())->adaptRequest($request, $query);
    }

    private function queryForAlias(string $alias): SelectQuery
    {
        $repository = $this->createStub(Table::class);
        $repository->method('getAlias')->willReturn($alias);
        $query = $this->createMock(SelectQuery::class);
        $query->method('getRepository')->willReturn($repository);

        return $query;
    }

    private function stubQueryForAlias(string $alias): SelectQuery
    {
        $repository = $this->createStub(Table::class);
        $repository->method('getAlias')->willReturn($alias);
        $query = $this->createStub(SelectQuery::class);
        $query->method('getRepository')->willReturn($repository);

        return $query;
    }
}
