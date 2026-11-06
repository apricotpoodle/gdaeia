<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\User;
use App\Model\Entity\ValidationCircuitRule;
use App\Policy\ValidationCircuitRulePolicy;
use App\Service\ValidationCircuitAdministrationService;
use Cake\TestSuite\TestCase;
use DomainException;

/** Vérifie les contraintes MySQL, les périmètres exacts et le service transactionnel. */
final class ValidationCircuitRulesTableTest extends TestCase
{
    protected array $fixtures = [
        'app.Departments', 'app.Roles', 'app.Users', 'app.UserDepartments',
        'app.Contracttypes', 'app.Validationsequences', 'app.ValidationCircuitRules',
        'app.ValidationCircuitManagers',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $connection = $this->getTableLocator()->get('Departments')->getConnection();
        foreach (['departments', 'roles', 'users', 'contracttypes', 'validationsequences'] as $table) {
            $connection->update($table, ['deleted' => null], ['id' => 1]);
        }
    }

    public function testLeFinderEtLaPolicyExigentLeMemeCoupleHabilitationRattachement(): void
    {
        $locator = $this->getTableLocator();
        $managers = $locator->get('ValidationCircuitManagers');
        $managers->saveOrFail($managers->newEntity(['role_id' => 1, 'department_id' => 1]));
        $operator = new User(['id' => 1, 'role_id' => 1, 'issuperuser' => false]);
        $policy = new ValidationCircuitRulePolicy();
        $departments = $locator->get('Departments');

        $this->assertSame([1], $departments->find('circuitManageableTo', user: $operator)
            ->all()->extract('id')->toList());
        $this->assertTrue($policy->canManage($operator, new ValidationCircuitRule(['department_id' => 1])));
        $this->assertFalse($policy->canManage($operator, new ValidationCircuitRule(['department_id' => 2])));

        // Le même rôle habilité ne suffit pas à un utilisateur non rattaché.
        $other = new User(['id' => 2, 'role_id' => 1, 'issuperuser' => false]);
        $this->assertSame(0, $departments->find('circuitManageableTo', user: $other)->count());
        $this->assertFalse($policy->canIndex($other, new ValidationCircuitRule()));

        // L'association au parent n'accorde pas son enfant pourtant habilité.
        $managers->saveOrFail($managers->newEntity(['role_id' => 1, 'department_id' => 2]));
        $this->assertSame([1], $departments->find('circuitManageableTo', user: $operator)
            ->all()->extract('id')->toList());
    }

    public function testLesReglesRefusentLesChampsAbsentsLesReferencesInconnuesEtLesDoublons(): void
    {
        $table = $this->getTableLocator()->get('ValidationCircuitRules');
        $empty = $table->newEntity([]);
        $this->assertFalse($table->save($empty));
        $this->assertArrayHasKey('department_id', $empty->getErrors());
        $invalid = $table->newEntity(['department_id' => 1, 'contracttype_id' => 1, 'final_role_id' => 999]);
        $this->assertFalse($table->save($invalid));
        $values = ['department_id' => 1, 'contracttype_id' => 1, 'final_role_id' => 1];
        $table->saveOrFail($table->newEntity($values));
        $duplicate = $table->newEntity($values);
        $this->assertFalse($table->save($duplicate));
        $this->assertSame(1, $table->find()->count());
    }

    public function testLesHabilitationsRefusentLesDoublonsEtLesReferencesInconnues(): void
    {
        $table = $this->getTableLocator()->get('ValidationCircuitManagers');
        $values = ['department_id' => 1, 'role_id' => 1];
        $table->saveOrFail($table->newEntity($values));
        $this->assertFalse($table->save($table->newEntity($values)));
        $this->assertFalse($table->save($table->newEntity(['department_id' => 1, 'role_id' => 999])));
        $this->assertSame(1, $table->find()->count());
    }

    public function testUnRoleGestionnaireDesactivePerdSonPerimetre(): void
    {
        $locator = $this->getTableLocator();
        $table = $locator->get('ValidationCircuitManagers');
        $table->saveOrFail($table->newEntity(['department_id' => 1, 'role_id' => 1]));
        $locator->get('Roles')->updateAll(['deleted' => '2026-11-06 10:00:00'], ['id' => 1]);
        $operator = new User(['id' => 1, 'role_id' => 1, 'issuperuser' => false]);

        $this->assertSame(0, $locator->get('Departments')->find('circuitManageableTo', user: $operator)->count());
    }

    public function testUneConfigurationInvalideDansLeLotNeLaisseAucuneRegle(): void
    {
        $locator = $this->getTableLocator();
        $locator->get('Validationsequences')->getConnection()->insert('validationsequences', [
            'department_id' => 2, 'role_id' => 1, 'sequence' => 1, 'name' => '',
        ]);
        $operator = new User(['id' => 1, 'issuperuser' => true]);
        try {
            (new ValidationCircuitAdministrationService())->create($operator, [1, 2], 1, 1);
            $this->fail('Le département sans validateur doit bloquer le lot.');
        } catch (DomainException $exception) {
            $this->assertStringContainsString('validateur', $exception->getMessage());
        }
        $this->assertSame(0, $locator->get('ValidationCircuitRules')->find()->count());
    }
}
