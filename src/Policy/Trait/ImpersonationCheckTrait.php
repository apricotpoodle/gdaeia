<?php
declare(strict_types=1);

namespace App\Policy\Trait;

use App\Model\Entity\User;
use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;

trait ImpersonationCheckTrait
{
    /**
     * Vérifie de manière stricte si l'utilisateur courant est en mode impersonation.
     *
     * @param \Authorization\IdentityInterface $user Identité envoyée par Authorization
     * @return bool
     */
    protected function isImpersonating(IdentityInterface $user): bool
    {
        $entity = $user->getOriginalData();

        if (!($entity instanceof EntityInterface)) {
            return false;
        }

        return $entity->has(User::FIELD_IS_IMPERSONATING)
            && $entity->get(User::FIELD_IS_IMPERSONATING) === true
            && $entity->has(User::FIELD_ORIGINAL_ADMIN_ID)
            && !empty($entity->get(User::FIELD_ORIGINAL_ADMIN_ID));
    }

    /**
     * Récupère l'ID de l'administrateur d'origine si en mode impersonation.
     *
     * @param \Authorization\IdentityInterface $user
     * @return string|int|null
     */
    protected function getOriginalAdminId(IdentityInterface $user): int|string|null
    {
        if (!$this->isImpersonating($user)) {
            return null;
        }

        /** @var \Cake\Datasource\EntityInterface $entity */
        $entity = $user->getOriginalData();

        return $entity->get(User::FIELD_ORIGINAL_ADMIN_ID);
    }
}
