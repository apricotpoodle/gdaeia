<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Applicationvalidationstep Entity
 *
 * @property int $id
 * @property int $applicationform_id
 * @property int $role_id
 * @property int|null $validationstatus_id
 * @property string|null $comment
 * @property int $validationsequence_id
 * @property int|null $validation_workflow_run_id
 * @property int|null $sequence_number
 * @property string|null $state
 * @property \Cake\I18n\DateTime|null $due_at
 * @property \Cake\I18n\DateTime|null $activated_at
 * @property \Cake\I18n\DateTime|null $completed_at
 * @property int $reminder_count
 * @property \Cake\I18n\DateTime|null $last_reminded_at
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\Applicationform $applicationform
 * @property \App\Model\Entity\Role $role
 * @property \App\Model\Entity\Validationstatus|null $validationstatus
 * @property \App\Model\Entity\Validationsequence $validationsequence
 * @property \App\Model\Entity\ValidationWorkflowRun|null $validation_workflow_run
 * @property \App\Model\Entity\Validation|null $validation
 */
class Applicationvalidationstep extends Entity
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
        'applicationform_id' => true,
        'role_id' => true,
        'validationstatus_id' => true,
        'comment' => true,
        'validationsequence_id' => true,
        'validation_workflow_run_id' => true,
        'sequence_number' => true,
        'state' => true,
        'due_at' => true,
        'activated_at' => true,
        'completed_at' => true,
        'reminder_count' => true,
        'last_reminded_at' => true,
        'deleted' => true,
        'modified' => true,
        'created' => true,
        'applicationform' => true,
        'role' => true,
        'validationstatus' => true,
        'validationsequence' => true,
    ];
}
