<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Controller\Api\ApplicationformsController;
use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\Department;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Entity\ValidationWorkflowRun;
use App\Policy\ApplicationformPolicy;
use App\Service\Security\PermissionResolver;
use Authorization\AuthorizationService;
use Authorization\Exception\ForbiddenException;
use Authorization\Policy\MapResolver;
use Cake\Database\Connection;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/** Exécute les actions API et leurs vraies Policies sans bootstrap de migration. */
final class ApplicationformWorkflowAuthorizationTest extends TestCase
{
    private const TABLES = [
        'ValidationWorkflowRuns', 'Applicationvalidationsteps', 'Validationsequences',
        'Roles', 'Users', 'Validations', 'WorkflowSettings', 'Applicationforms', 'RolePermissions',
        'ValidationCommentTemplates',
    ];

    protected function tearDown(): void
    {
        foreach (self::TABLES as $alias) {
            TableRegistry::getTableLocator()->remove($alias);
        }
        parent::tearDown();
    }

    public function testLeVoteProxyApiRefuseLaPermissionAbsenteAvantLeWorkflow(): void
    {
        $controller = $this->controller([
            'applicationforms.read' => ['self'],
            'applicationforms.vote' => ['unscoped'],
        ], 'POST', [
            'step_id' => 5, 'decision' => 'accepter', 'override' => true,
        ]);
        $this->expectException(ForbiddenException::class);

        $controller->voteValidation('42');
    }

    /** @return array<string, array{array<string, list<string>>, array<string, bool>}> */
    public static function blockedActionGrants(): array
    {
        return [
            'aucune action' => [[], ['overrideValidation' => false, 'resendValidation' => false]],
            'override seul' => [
                ['applicationforms.override_validation' => ['unscoped']],
                ['overrideValidation' => true, 'resendValidation' => false],
            ],
            'relance seule' => [
                ['applicationforms.remind_validation' => ['unscoped']],
                ['overrideValidation' => false, 'resendValidation' => true],
            ],
            'scopes incorrects' => [
                [
                    'applicationforms.override_validation' => ['all'],
                    'applicationforms.remind_validation' => ['all'],
                ],
                ['overrideValidation' => false, 'resendValidation' => false],
            ],
        ];
    }

    /** @param array<string, list<string>> $grants @param array<string, bool> $expected */
    #[DataProvider('blockedActionGrants')]
    public function testLesDroitsDesCyclesBloquesRespectentLesPermissionsIndependantes(
        array $grants,
        array $expected,
    ): void {
        $controller = $this->controller($grants + ['applicationforms.read' => ['self']]);

        $controller->blockedValidations();

        $rows = $controller->viewBuilder()->getVars()['data'];
        $this->assertCount(1, $rows);
        $this->assertSame(42, $rows[0]['id']);
        $this->assertSame($expected, $rows[0]['grid_rights']['actions']);
    }

    public function testLEtatApiMasqueLOverrideEtLesEtapesDUnAutreRoleSansPermission(): void
    {
        $controller = $this->controller(['applicationforms.read' => ['self']]);

        $controller->validationState('42');

        $steps = $controller->viewBuilder()->getVars()['steps'];
        $this->assertCount(1, $steps);
        $this->assertSame(6, $steps[0]['id']);
        $this->assertFalse($steps[0]['can_override']);
        $this->assertFalse($steps[0]['can_vote']);
        $this->assertFalse($steps[0]['is_proxy_vote']);
    }

    public function testLEtatApiExposeLOverrideAccordePourLesEtapesActives(): void
    {
        $controller = $this->controller([
            'applicationforms.read' => ['self'],
            'applicationforms.override_validation' => ['unscoped'],
        ]);

        $controller->validationState('42');

        $steps = $controller->viewBuilder()->getVars()['steps'];
        $this->assertSame([5, 6], array_column($steps, 'id'));
        $this->assertTrue($steps[0]['can_override']);
        $this->assertTrue($steps[0]['can_vote']);
        $this->assertTrue($steps[0]['is_proxy_vote']);
    }

    /** @param array<string, list<string>> $grants @param array<string, mixed> $data */
    private function controller(array $grants, string $method = 'GET', array $data = []): ApplicationformsController
    {
        $actor = new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]);
        $actor->setAuthorization(new AuthorizationService(new MapResolver([
            Applicationform::class => new ApplicationformPolicy(new PermissionResolver($grants)),
        ])));
        $form = new Applicationform([
            'id' => 42, 'user_id' => 10, 'department_id' => 8,
            'department' => new Department(['id' => 8, 'name' => 'Département de test']),
        ]);
        $step = new Applicationvalidationstep([
            'id' => 5, 'applicationform_id' => 42, 'role_id' => 4,
            'sequence_number' => 1, 'state' => 'en_attente',
            'activated_at' => new DateTime('2026-09-10 10:00:00'),
            'applicationform' => $form, 'role' => new Role(['id' => 4, 'name' => 'Valideur']),
        ]);
        $connection = $this->createStub(Connection::class);
        $connection->method('transactional')->willReturnCallback(static fn(callable $callback): mixed => $callback());
        $ownRoleStep = new Applicationvalidationstep([
            'id' => 6, 'applicationform_id' => 42, 'role_id' => User::ROLE_ADMIN,
            'sequence_number' => 2, 'state' => 'en_attente',
            'activated_at' => new DateTime('2026-09-10 10:00:00'),
            'applicationform' => $form, 'role' => new Role(['id' => User::ROLE_ADMIN, 'name' => 'Administrateur']),
        ]);
        foreach (self::TABLES as $alias) {
            $query = $this->createStub(SelectQuery::class);
            foreach (['where', 'contain', 'orderByAsc', 'epilog', 'select', 'innerJoinWith'] as $queryMethod) {
                $query->method($queryMethod)->willReturnSelf();
            }
            $query->method('first')->willReturn($alias === 'ValidationWorkflowRuns'
                ? new ValidationWorkflowRun(['id' => 3, 'applicationform_id' => 42, 'state' => 'en_attente'])
                : null);
            $query->method('count')->willReturn(0);
            $query->method('all')->willReturn(new ResultSet($alias === 'Applicationvalidationsteps' ? [$step, $ownRoleStep] : []));
            $table = $this->createStub(Table::class);
            $table->method('find')->willReturn($query);
            $table->method('get')->willReturn($form);
            $table->method('getConnection')->willReturn($connection);
            $table->method('newEmptyEntity')->willReturn(new Applicationform());
            TableRegistry::getTableLocator()->set($alias, $table);
        }
        $permissionTable = $this->createStub(Table::class);
        $permissionTable->method('find')->willReturnCallback(function (string $finder, mixed ...$options) use ($grants): SelectQuery {
            $query = $this->createStub(SelectQuery::class);
            $query->method('all')->willReturn(new ResultSet(array_map(
                static fn(string $scope): Entity => new Entity(['scope_type' => $scope]),
                $grants[$options['code']] ?? [],
            )));

            return $query;
        });
        TableRegistry::getTableLocator()->remove('RolePermissions');
        TableRegistry::getTableLocator()->set('RolePermissions', $permissionTable);

        return new ApplicationformsController((new ServerRequest(['environment' => ['REQUEST_METHOD' => $method]]))
            ->withAttribute('identity', $actor)->withParsedBody($data));
    }
}
