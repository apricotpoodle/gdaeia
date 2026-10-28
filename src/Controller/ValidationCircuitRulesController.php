<?php
declare(strict_types=1);

namespace App\Controller;

/** Écran d'administration des fins de circuit locales. */
final class ValidationCircuitRulesController extends AppController
{
    /** Affiche l'administration des fins de circuit locales. */
    public function index(): void
    {
        $this->Authorization->authorize($this->fetchTable('ValidationCircuitRules')->newEmptyEntity(), 'index');
    }
}
