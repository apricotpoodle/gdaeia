<?php
declare(strict_types=1);

namespace App\Test\TestCase\Integration\Service;

use App\Service\DepartmentManagementService;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use DomainException;

final class DepartmentManagementServiceIntegrationTest extends TestCase
{
    protected array $fixtures = [
        'app.Departments',
        'app.CgrCodes',
        'app.DepartmentCgrCodes',
        'app.DepartmentManagers',
        'app.CgrStrategies',
        'app.Users',
        'app.UserDepartments',
        'app.Applicationforms',
        'app.Validationsequences',
    ];

    public function testLImpactExposeLesDependancesDuDepartement(): void
    {
        $department = $this->getTableLocator()->get('Departments')->get(2);

        $impact = (new DepartmentManagementService())->getImpact($department);

        $this->assertArrayHasKey('dependencies', $impact);
        $this->assertArrayHasKey('blockers', $impact);
        $this->assertArrayHasKey('can_deactivate', $impact);
        $this->assertIsBool($impact['can_deactivate']);
    }

    /** Vérifie le remplacement atomique des deux collections sans supprimer leurs cibles. */
    public function testLesAssociationsSontRemplaceesSansSupprimerLesEntitesCibles(): void
    {
        $departments = $this->getTableLocator()->get('Departments');
        $department = $departments->get(2);

        $result = (new DepartmentManagementService())->save($department, [
            'code' => $department->code,
            'name' => $department->name,
            'department_type_id' => $department->department_type_id,
            'manager_ids' => [1],
            'cgr_code_ids' => [1],
        ]);

        $this->assertSame(2, (int)$result->id);
        $this->assertSame(1, (int)$this->getTableLocator()->get('Users')->get(1)->id);
        $this->assertSame(1, (int)$this->getTableLocator()->get('CgrCodes')->get(1)->id);
        $this->assertSame(1, (int)ConnectionManager::get('test')->execute(
            'SELECT COUNT(*) FROM department_managers WHERE department_id = 2 AND user_id = 1',
        )->fetchColumn(0));
        $this->assertSame(1, (int)ConnectionManager::get('test')->execute(
            'SELECT COUNT(*) FROM department_cgr_codes WHERE department_id = 2 AND cgr_code_id = 1',
        )->fetchColumn(0));

        (new DepartmentManagementService())->save($result, [
            'code' => $result->code,
            'name' => $result->name,
            'department_type_id' => $result->department_type_id,
            'manager_ids' => [],
            'cgr_code_ids' => [],
        ]);

        $this->assertSame(0, (int)ConnectionManager::get('test')->execute(
            'SELECT COUNT(*) FROM department_managers WHERE department_id = 2',
        )->fetchColumn(0));
        $this->assertSame(0, (int)ConnectionManager::get('test')->execute(
            'SELECT COUNT(*) FROM department_cgr_codes WHERE department_id = 2',
        )->fetchColumn(0));
        $this->assertNotNull($this->getTableLocator()->get('Users')->find()->where(['id' => 1])->first());
        $this->assertNotNull($this->getTableLocator()->get('CgrCodes')->find()->where(['id' => 1])->first());
    }

    /** Vérifie que le nettoyage sélectionné est atomique et détache sans désactiver les utilisateurs. */
    public function testLeNettoyageDesDependancesEtLaDesactivationSontAtomiques(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-10-16 10:00:00';
        $connection->update('departments', ['base' => 0, 'deleted' => null], ['id' => 2]);
        $connection->update('users', ['deleted' => null], ['id' => 1]);
        $connection->insert('user_departments', [
            'id' => 2,
            'user_id' => 1,
            'department_id' => 2,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validationsequences', [
            'id' => 2,
            'department_id' => 2,
            'name' => 'Séquence à nettoyer',
            'role_id' => 1,
            'sequence' => 1,
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('cgr_codes', [
            'id' => 2,
            'type' => 'test',
            'code' => 'CGR-TEST',
            'label' => 'Code à nettoyer',
            'active' => 1,
            'is_system' => 0,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('department_cgr_codes', [
            'department_id' => 2,
            'cgr_code_id' => 2,
        ]);

        $department = $this->getTableLocator()->get('Departments')->get(2);
        $result = (new DepartmentManagementService())->deactivateWithCleanup(
            $department,
            ['users', 'validationsequences', 'cgr_codes'],
        );

        $this->assertSame(2, $result['department_ids'][0]);
        $this->assertSame(0, (int)$connection->execute(
            'SELECT COUNT(*) FROM user_departments WHERE department_id = 2',
        )->fetchColumn(0));
        $this->assertNotNull($connection->execute(
            'SELECT deleted FROM validationsequences WHERE id = 2',
        )->fetchColumn(0));
        $this->assertFalse((bool)$connection->execute(
            'SELECT active FROM cgr_codes WHERE id = 2',
        )->fetchColumn(0));
        $this->assertNotNull($connection->execute(
            'SELECT deleted FROM departments WHERE id = 2',
        )->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute(
            'SELECT COUNT(*) FROM users WHERE id = 1 AND deleted IS NOT NULL',
        )->fetchColumn(0));
    }

    /** Vérifie qu’un formulaire actif reste un blocage non nettoyable. */
    public function testUnFormulaireActifEmpêcheLeNettoyageEtLaDesactivation(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('departments', ['base' => 0, 'deleted' => null], ['id' => 2]);
        $connection->update('applicationforms', ['department_id' => 2, 'deleted' => null], ['id' => 1]);
        $department = $this->getTableLocator()->get('Departments')->get(2);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('formulaires actifs');

        (new DepartmentManagementService())->deactivateWithCleanup(
            $department,
            ['users', 'validationsequences', 'cgr_codes'],
        );
    }
}
