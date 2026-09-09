<?php
declare(strict_types=1);

namespace App\Policy\Trait;

use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;

trait ImpersonationCheckTrait
{
    /**
     * Vérifie de manière stricte si l'utilisateur courant est en mode impersonation.
     *
     * @param IdentityInterface $user Identité envoyée par Authorization
     * @return bool
     */
    protected function isImpersonating(IdentityInterface $user): bool
    {
        /** @var mixed $entity */
        $entity = $user->getOriginalData();

        if (!($entity instanceof EntityInterface)) {
            return false;
        }

        return $entity->has('is_impersonating')
            && $entity->get('is_impersonating') === true
            && $entity->has('original_admin_id')
            && !empty($entity->get('original_admin_id'));
    }

    /**
     * Récupère l'ID de l'administrateur d'origine si en mode impersonation.
     *
     * @param IdentityInterface $user
     * @return int|string|null
     */
    protected function getOriginalAdminId(IdentityInterface $user): int|string|null
    {
        if (!$this->isImpersonating($user)) {
            return null;
        }

        /** @var \Cake\Datasource\EntityInterface $entity */
        $entity = $user->getOriginalData();

        return $entity->get('original_admin_id');
    }
}
