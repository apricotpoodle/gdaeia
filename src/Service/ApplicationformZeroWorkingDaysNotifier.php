<?php
declare(strict_types=1);

namespace App\Service;

use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\Applicationform;
use App\Model\Entity\Department;
use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;

/** Notifie les personnes autorisées lorsqu'une DAE est enregistrée avec zéro jour travaillé. */
final class ApplicationformZeroWorkingDaysNotifier
{
    /** Envoie une notification distincte après une sauvegarde réussie avec zéro jour. */
    public function notify(Applicationform $savedApplicationform): void
    {
        if (!$this->hasZeroWorkingDays($savedApplicationform)) {
            return;
        }

        /** @var \App\Model\Table\ApplicationformsTable $applicationforms */
        $applicationforms = TableRegistry::getTableLocator()->get('Applicationforms');
        $applicationform = $applicationforms->get(
            $savedApplicationform->get(Applicationform::FIELD_ID),
            contain: ['Users', 'Departments' => ['Managers']],
        );

        $recipients = [];
        $author = $applicationform->get('user');
        if ($author instanceof User) {
            $recipients[] = $author;
        }

        $department = $applicationform->get('department');
        if ($department instanceof Department) {
            foreach ($department->get('managers') ?? [] as $manager) {
                if ($manager instanceof User) {
                    $recipients[] = $manager;
                }
            }
        }

        $departmentId = (int)$applicationform->get(Applicationform::FIELD_DEPARTMENT_ID);
        /** @var \App\Model\Table\UsersTable $users */
        $users = TableRegistry::getTableLocator()->get('Users');
        $administrators = $users->find()
            ->innerJoinWith('UserDepartments', static function (SelectQuery $query) use ($departmentId): SelectQuery {
                return $query->where(['UserDepartments.department_id' => $departmentId]);
            })
            ->where([
                'Users.role_id' => User::ROLE_ADMIN,
                'Users.deleted IS' => null,
            ])
            ->distinct(['Users.id'])
            ->all();
        foreach ($administrators as $administrator) {
            if ($administrator instanceof User) {
                $recipients[] = $administrator;
            }
        }

        $sent = [];
        foreach ($recipients as $recipient) {
            $email = $recipient->get(User::FIELD_EMAIL);
            if (
                $recipient->get(User::FIELD_DELETED) !== null
                || !is_string($email)
                || trim($email) === ''
            ) {
                continue;
            }

            $deduplicationKey = strtolower(trim($email));
            if (isset($sent[$deduplicationKey])) {
                continue;
            }
            $sent[$deduplicationKey] = true;

            (new ValidationWorkflowMailer())->safeSend('zeroWorkingDays', [$recipient, $applicationform]);
        }
    }

    /** Vérifie que la valeur persistée correspond au zéro numérique. */
    private function hasZeroWorkingDays(Applicationform $applicationform): bool
    {
        $value = $applicationform->get(Applicationform::FIELD_WORKING_DAYS);

        return $value === 0 || $value === '0';
    }
}
