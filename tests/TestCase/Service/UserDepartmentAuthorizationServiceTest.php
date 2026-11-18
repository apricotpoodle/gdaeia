<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\User;
use App\Model\Entity\UserDepartment;
use App\Service\UserDepartmentAuthorizationService;
use Cake\TestSuite\TestCase;

final class UserDepartmentAuthorizationServiceTest extends TestCase
{
    public function testUnOrdreDifferentNeConstituePasUneModificationDesAssociations(): void
    {
        $before = new User(['user_departments' => [
            ['department_id' => 2],
            ['department_id' => 3],
        ]]);
        $after = new User(['user_departments' => [
            ['department_id' => 3],
            ['department_id' => 2],
        ]]);

        $service = new UserDepartmentAuthorizationService();

        $this->assertFalse($service->hasAssociationChanges(
            $service->snapshotDepartmentIds($before),
            $after,
        ));
    }

    public function testUnAjoutOuRetraitConstitueUneModificationDesAssociations(): void
    {
        $before = new User(['user_departments' => [['department_id' => 2]]]);
        $after = new User(['user_departments' => [['department_id' => 3]]]);

        $service = new UserDepartmentAuthorizationService();

        $this->assertTrue($service->hasAssociationChanges(
            $service->snapshotDepartmentIds($before),
            $after,
        ));
    }

    public function testLeSnapshotResteFiableQuandUneAssociationIdentifieeChangeEnPlace(): void
    {
        $association = new UserDepartment();
        $association->set('id', 41);
        $association->set('department_id', 2);
        $before = new User(['user_departments' => [$association]]);
        $after = clone $before;
        $service = new UserDepartmentAuthorizationService();
        $beforeDepartmentIds = $service->snapshotDepartmentIds($before);

        $association->set('department_id', 4);

        $this->assertSame(41, $association->get('id'));
        $this->assertSame([2], $beforeDepartmentIds);
        $this->assertTrue($service->hasAssociationChanges($beforeDepartmentIds, $after));
    }
}
