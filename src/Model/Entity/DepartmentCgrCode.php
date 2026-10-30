<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Association entre un département et un code CGR. */
class DepartmentCgrCode extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'department_id' => true,
        'cgr_code_id' => true,
        'department' => true,
        'cgr_code' => true,
    ];

    public const FIELD_DEPARTMENT_ID = 'department_id';
    public const FIELD_CGR_CODE_ID = 'cgr_code_id';
}
