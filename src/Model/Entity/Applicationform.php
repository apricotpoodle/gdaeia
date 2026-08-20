<?php
declare(strict_types=1);

namespace App\Model\Entity;

public const ALLOWED_ROLES_FOR_EDIT   = [self::ROLE_ADMIN];

/**
 * Fields that can be mass assigned using newEntity() or patchEntity().
 *
 * Note that when '*' is set to true, this allows all unspecified fields to
 * be mass assigned. For security purposes, it is advised to set '*' to false
 * (or remove it), and explicitly make individual fields accessible as needed.
 *
 * @var array<string, bool>
 */
protected array $_accessible = [
        'department_id' => true,
        'user_id' => true,
        'cgr' => true,
        'contracttype_id' => true,
        'hiringreason_id' => true,
        'reasonforreplacement' => true,
        'budgetfeature_id' => true,
        'jobtitle' => true,
        'professionalcategory_id' => true,
        'worktime_id' => true,
        'workingtimedistribution' => true,
        'grossremuneration' => true,
        'period_id' => true,
        'qualification' => true,
        'begin_at' => true,
        'end_at' => true,
        'applicantname' => true,
        'yesno_id' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'department' => true,
        'user' => true,
        'contracttype' => true,
        'hiringreason' => true,
        'budgetfeature' => true,
        'professionalcategory' => true,
        'worktime' => true,
        'period' => true,
        'yesno' => true,
        'applicationformstatuses' => true,
        'applicationvalidationsteps' => true,
        'currentvalidationroles' => true,
        'validation_visas' => true,
        'validations' => true,
        'collaborator_id' => true,
        'archived' => true,
        'comments' => true,
    ];
}
