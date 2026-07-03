<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Applicationform Entity
 *
 * @property int $id
 * @property int $department_id
 * @property int $user_id
 * @property string|null $cgr
 * @property int $contracttype_id
 * @property int $hiringreason_id
 * @property string|null $reasonforreplacement
 * @property int $budgetfeature_id
 * @property string $jobtitle
 * @property int $professionalcategory_id
 * @property int $worktime_id
 * @property string|null $workingtimedistribution
 * @property string $grossremuneration
 * @property int $period_id
 * @property string|null $qualification
 * @property \Cake\I18n\Date|null $begin_at
 * @property \Cake\I18n\Date|null $end_at
 * @property string|null $applicantname
 * @property int $yesno_id
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Contracttype $contracttype
 * @property \App\Model\Entity\Hiringreason $hiringreason
 * @property \App\Model\Entity\Budgetfeature $budgetfeature
 * @property \App\Model\Entity\Professionalcategory $professionalcategory
 * @property \App\Model\Entity\Worktime $worktime
 * @property \App\Model\Entity\Period $period
 * @property \App\Model\Entity\Yesno $yesno
 * @property \App\Model\Entity\Applicationformstatus[] $applicationformstatuses
 * @property \App\Model\Entity\Applicationvalidationstep[] $applicationvalidationsteps
 * @property \App\Model\Entity\Currentvalidationrole[] $currentvalidationroles
 * @property \App\Model\Entity\ValidationVisa[] $validation_visas
 * @property \App\Model\Entity\Validation[] $validations
 */
class Applicationform extends Entity
{
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
    ];
}
