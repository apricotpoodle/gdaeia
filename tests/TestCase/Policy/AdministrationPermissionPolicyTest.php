<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\CgrCode;
use App\Model\Entity\Contracttype;
use App\Model\Entity\FieldAuthorization;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Entity\ValidationCommentTemplate;
use App\Model\Entity\WorkflowSetting;
use App\Model\Table\FieldAuthorizationsTable;
use App\Policy\BudgetfeaturePolicy;
use App\Policy\CgrCodePolicy;
use App\Policy\ContracttypePolicy;
use App\Policy\FieldAuthorizationPolicy;
use App\Policy\FieldAuthorizationsTablePolicy;
use App\Policy\HiringreasonPolicy;
use App\Policy\PeriodPolicy;
use App\Policy\ProfessionalcategoryPolicy;
use App\Policy\RolePolicy;
use App\Policy\ValidationCommentTemplatePolicy;
use App\Policy\WorkflowSettingPolicy;
use App\Policy\WorktimePolicy;
use App\Policy\YesnoPolicy;
use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/** Vérifie les permissions par action sans charger de fixture ni de connexion. */
final class AdministrationPermissionPolicyTest extends TestCase
{
    /** @return iterable<string, array{class-string, class-string, string, string}> */
    public static function actions(): iterable
    {
        $crud = ['canIndex' => 'read', 'canView' => 'read', 'canAdd' => 'create',
            'canEdit' => 'update', 'canDelete' => 'delete'];
        $references = [ContracttypePolicy::class, HiringreasonPolicy::class, ProfessionalcategoryPolicy::class,
            WorktimePolicy::class, PeriodPolicy::class, BudgetfeaturePolicy::class, YesnoPolicy::class];
        foreach ($references as $policy) {
            foreach ($crud as $method => $action) {
                yield $policy . $method => [$policy, Contracttype::class, $method, 'references.' . $action];
            }
        }
        foreach ([
            [CgrCodePolicy::class, CgrCode::class, 'references', $crud + ['canDeactivate' => 'delete']],
            [RolePolicy::class, Role::class, 'roles', $crud],
            [FieldAuthorizationPolicy::class, FieldAuthorization::class, 'fieldauthorizations', $crud],
            [FieldAuthorizationsTablePolicy::class, FieldAuthorizationsTable::class,
                'fieldauthorizations', ['canIndex' => 'read']],
            [ValidationCommentTemplatePolicy::class, ValidationCommentTemplate::class,
                'validationcommenttemplates', $crud],
            [WorkflowSettingPolicy::class, WorkflowSetting::class,
                'workflowsettings', ['canIndex' => 'read', 'canManage' => 'update']],
        ] as [$policy, $entity, $resource, $actions]) {
            foreach ($actions as $method => $action) {
                yield $policy . $method => [$policy, $entity, $method, $resource . '.' . $action];
            }
        }
    }

    #[DataProvider('actions')]
    public function testChaqueActionExigeSaPermissionEtLePerimetreSansFiltre(
        string $policyClass,
        string $entityClass,
        string $method,
        string $code,
    ): void {
        $entity = $entityClass === FieldAuthorizationsTable::class
            ? $this->createStub($entityClass)
            : new $entityClass(['base' => false, 'active' => true, 'is_system' => false]);
        $operator = new User(['role_id' => User::ROLE_ADMIN]);
        $granted = new $policyClass(new PermissionResolver([$code => ['unscoped']]));
        $denied = new $policyClass(new PermissionResolver([]));
        $wrongScope = new $policyClass(new PermissionResolver([$code => ['assigned']]));
        $otherAction = new $policyClass(new PermissionResolver(['menus.manage' => ['unscoped']]));

        $this->assertTrue($granted->$method($operator, $entity));
        $this->assertFalse($denied->$method($operator, $entity));
        $this->assertFalse($wrongScope->$method($operator, $entity));
        $this->assertFalse($otherAction->$method($operator, $entity));
        $this->assertTrue($denied->$method(new User(['issuperuser' => true]), $entity));
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn([]);
        $this->assertFalse($granted->$method($identity, $entity));
    }

    public function testLesPermissionsNeLeventPasLesProtectionsDesSoclesEtCodesSysteme(): void
    {
        $permissions = new PermissionResolver([
            'references.update' => ['unscoped'], 'references.delete' => ['unscoped'],
            'roles.update' => ['unscoped'], 'roles.delete' => ['unscoped'],
        ]);
        foreach ([new User(['role_id' => User::ROLE_ADMIN]), new User(['issuperuser' => true])] as $operator) {
            $reference = new Contracttype(['base' => true]);
            $role = new Role(['base' => true]);
            $this->assertTrue((new ContracttypePolicy($permissions))->canEdit($operator, $reference));
            $this->assertFalse((new ContracttypePolicy($permissions))->canDelete($operator, $reference));
            $this->assertTrue((new RolePolicy($permissions))->canEdit($operator, $role));
            $this->assertFalse((new RolePolicy($permissions))->canDelete($operator, $role));
            $cgr = new CgrCodePolicy($permissions);
            $system = new CgrCode(['active' => true, 'is_system' => true]);
            $inactive = new CgrCode(['active' => false, 'is_system' => false]);
            $this->assertFalse($cgr->canEdit($operator, $system));
            $this->assertFalse($cgr->canDeactivate($operator, $system));
            $this->assertFalse($cgr->canDeactivate($operator, $inactive));
        }
    }

    public function testLaLectureNeDonneAucuneMutationEtLesCodesInactifsRestentInvisibles(): void
    {
        $operator = new User(['role_id' => User::ROLE_ADMIN]);
        $policy = new CgrCodePolicy(new PermissionResolver(['references.read' => ['unscoped']]));
        $active = new CgrCode(['active' => true, 'is_system' => false]);
        $inactive = new CgrCode(['active' => false, 'is_system' => false]);
        $this->assertTrue($policy->canView($operator, $active));
        $this->assertFalse($policy->canView($operator, $inactive));
        $this->assertFalse($policy->canIndex($operator, $inactive));
        $this->assertFalse($policy->canAdd($operator, $active));
        $this->assertFalse($policy->canEdit($operator, $active));
        $this->assertFalse($policy->canDelete($operator, $active));
        $this->assertTrue($policy->canAssociate($operator, $active));
    }
}
