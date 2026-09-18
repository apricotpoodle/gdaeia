<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Affiche l'écran d'administration des séquences de validation.
 *
 * @property \App\Model\Table\ValidationsequencesTable $Validationsequences
 */
class ValidationsequencesController extends AppController
{
    /** @return void */
    public function index(): void
    {
        $this->Authorization->authorize($this->Validationsequences->newEmptyEntity(), 'index');
    }
}
