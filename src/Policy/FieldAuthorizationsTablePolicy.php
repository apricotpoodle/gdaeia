<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Table\FieldAuthorizationsTable;
use Authorization\IdentityInterface;
// La ligne "use Cake\ORM\Query;" peut être retirée car elle n'est plus utile ici

class FieldAuthorizationsTablePolicy
{
    /**
     * Détermine si l'utilisateur peut lister les éléments (action index)
     *
     * @param \Authorization\IdentityInterface $user L'utilisateur connecté
     * @param \App\Model\Table\FieldAuthorizationsTable $table L'instance de la table
     * @return bool
     */
    public function canIndex(IdentityInterface $user, FieldAuthorizationsTable $table): bool
    {
        // AJOUTEZ CETTE LIGNE ICI :
        // dd('ARRIVÉ DANS LA POLICY !');
        // dd($user);
        /** @var \App\Policy\User $u */
        $u = $user->getOriginalData();
        // Exemple de logique (à adapter selon votre application) :
        if ($u->role_id != 1) {  // get('role_id') === 1) {
            // dd('result true pour table Policy');
            return true;
        }

        return false;
    }
}
