<?php

declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;
use Authorization\Policy\Result;

/**
 * Users policy
 */
class UserPolicy
{
    /**
     * Check if $user can list Users
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\User $users
     * @return bool
     */
    public function canIndex(IdentityInterface $user, User $users)
    {
        $msg = 'youpi!';
        $result = true;
        return  $result; // new Result($result, $msg);
    }

    /**
     * Check if $user can add Users
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\User $users
     * @return bool
     */
    public function canAdd(IdentityInterface $user, User $users) {}

    /**
     * Check if $user can edit Users
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\User $users
     * @return bool
     */
    public function canEdit(IdentityInterface $user, User $users) {}

    /**
     * Check if $user can delete Users
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\User $users
     * @return bool
     */
    public function canDelete(IdentityInterface $user, User $users) {}

    /**
     * Check if $user can view Users
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\User $users
     * @return bool
     */
    public function canView(IdentityInterface $user, User $users) {}
}
