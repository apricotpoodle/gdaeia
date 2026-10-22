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
            'department_id' => 2,
            'type' => 'test',
            'code' => 'CGR-TEST',
            'label' => 'Code à nettoyer',
            'active' => 1,
            'is_system' => 0,
            'created' => $now,
            'modified' => $now,
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
