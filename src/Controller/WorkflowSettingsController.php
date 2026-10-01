<?php
declare(strict_types=1);

namespace App\Controller;

/** Affiche le paramétrage global du workflow de validation. */
/**
 * @property \App\Model\Table\WorkflowSettingsTable $WorkflowSettings
 */
class WorkflowSettingsController extends AppController
{
    /** @return void */
    public function index(): void
    {
        $this->Authorization->authorize($this->fetchTable('WorkflowSettings')->newEmptyEntity(), 'index');
    }
}
