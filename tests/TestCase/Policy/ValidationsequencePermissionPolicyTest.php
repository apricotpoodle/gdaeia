<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Model\Entity\Validationsequence;
use App\Model\Table\UserDepartmentsTable;
use App\Policy\ValidationsequencePolicy;
use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/** Vérifie les actions départementales sans base de données. */
final class ValidationsequencePermissionPolicyTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function actions(): array
    {
        return [
            'lecture' => ['canView', 'validationsequences.read'],
            'création' => ['canAdd', 'validationsequences.create'],
            'modification' => ['canEdit', 'validationsequences.update'],
            'suppression' => ['canDelete', 'validationsequences.delete'],
        ];
    }

    #[DataProvider('actions')]
    public function testLesActionsExigentLeurAttributionEtUnDepartementDirect(string $method, string $code): void
    {
        $this->directDepartment(20);
        $operator = new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]);
        $within = new Validationsequence(['department_id' => 20]);
        $outside = new Validationsequence(['department_id' => 21]);
        $granted = new ValidationsequencePolicy(new PermissionResolver([$code => ['assigned']]));
        $denied = new ValidationsequencePolicy(new PermissionResolver([]));
        $wrong = new ValidationsequencePolicy(new PermissionResolver([$code => ['unscoped']]));
        $other = new ValidationsequencePolicy(new PermissionResolver(['menus.manage' => ['unscoped']]));

        $this->assertTrue(is_callable([$granted, $method]), 'Chaque action doit exposer sa propre autorisation.');
        $this->assertTrue($granted->$method($operator, $within));
        $this->assertFalse($granted->$method($operator, $outside));
        $this->assertFalse($denied->$method($operator, $within));
        $this->assertFalse($wrong->$method($operator, $within));
        $this->assertFalse($other->$method($operator, $within));
        $this->assertTrue($denied->$method(new User(['issuperuser' => true]), $outside));
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn([]);
        $this->assertFalse($granted->$method($identity, $within));
    }

    public function testLaLectureAccepteAllMaisNeDonneAucuneMutation(): void
    {
        $operator = new User(['role_id' => User::ROLE_ADMIN]);
        $sequence = new Validationsequence(['department_id' => 99]);
        $policy = new ValidationsequencePolicy(new PermissionResolver(['validationsequences.read' => ['all']]));
        $this->assertTrue(is_callable([$policy, 'canAdd']), 'La création exige sa propre autorisation.');
        $this->assertTrue($policy->canIndex($operator));
        $this->assertTrue($policy->canView($operator, $sequence));
        $this->assertFalse($policy->canAdd($operator, $sequence));
        $this->assertFalse($policy->canEdit($operator, $sequence));
        $this->assertFalse($policy->canDelete($operator, $sequence));
        $this->assertFalse($policy->canView($operator, new Validationsequence()));
    }

    public function testLaGestionRefuseAllEtAutoriseLEntreeSansCibleSeulementAvecAssigned(): void
    {
        $this->directDepartment(20);
        $operator = new User(['id' => 10]);
        foreach (['canAdd' => 'create', 'canEdit' => 'update', 'canDelete' => 'delete'] as $method => $action) {
            $all = new ValidationsequencePolicy(new PermissionResolver(['validationsequences.' . $action => ['all']]));
            $assigned = new ValidationsequencePolicy(new PermissionResolver([
                'validationsequences.' . $action => ['assigned'],
            ]));
            $this->assertTrue(is_callable([$all, $method]), 'La gestion exige des actions distinctes.');
            $this->assertFalse($all->$method($operator, new Validationsequence(['department_id' => 20])));
            $this->assertTrue($assigned->$method($operator, new Validationsequence()));
            $this->assertTrue($all->$method(new User(['issuperuser' => true]), new Validationsequence()));
        }
        $this->assertFalse((new ValidationsequencePolicy(new PermissionResolver([])))->canIndex($operator));
    }

    public function testUnPerimetreAllPreexistantNeContournePasAssignedPourLesMutations(): void
    {
        $this->directDepartment(20);
        $operator = new User(['id' => 10]);
        foreach (['canAdd' => 'create', 'canEdit' => 'update', 'canDelete' => 'delete'] as $method => $action) {
            $policy = new ValidationsequencePolicy(new PermissionResolver([
                'validationsequences.' . $action => ['assigned', 'all'],
            ]));
            $this->assertTrue($policy->$method($operator, new Validationsequence(['department_id' => 20])));
            $this->assertFalse($policy->$method($operator, new Validationsequence(['department_id' => 21])));
        }
    }

    /** Simule uniquement la lecture des associations ; le résolveur et la Policy restent réels. */
    private function directDepartment(int $departmentId): void
    {
        $conditions = [];
        $query = $this->createStub(SelectQuery::class);
        $query->method('where')->willReturnCallback(function (array $where) use (&$conditions, $query): SelectQuery {
            $conditions += $where;

            return $query;
        });
        $query->method('count')->willReturnCallback(static function () use (&$conditions, $departmentId): int {
            return ($conditions['user_id'] ?? null) === 10
                && (!isset($conditions['department_id']) || $conditions['department_id'] === $departmentId) ? 1 : 0;
        });
        $table = $this->createStub(UserDepartmentsTable::class);
        $table->method('find')->willReturnCallback(function () use (&$conditions, $query): SelectQuery {
            $conditions = [];

            return $query;
        });
        $this->getTableLocator()->set('UserDepartments', $table);
    }
}
