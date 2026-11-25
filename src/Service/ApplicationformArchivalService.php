<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Applicationform;
use App\Model\Entity\ValidationWorkflowRun;
use App\Model\Table\ApplicationformsTable;
use App\Model\Table\ValidationWorkflowRunsTable;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use RuntimeException;

/** Applique les transitions d'archivage des DAE dans une transaction. */
final class ApplicationformArchivalService
{
    private ApplicationformsTable $applicationforms;

    private ValidationWorkflowRunsTable $runs;

    /** Initialise les tables ou accepte leurs implémentations réelles. */
    public function __construct(
        ?ApplicationformsTable $applicationforms = null,
        ?ValidationWorkflowRunsTable $runs = null,
    ) {
        $locator = TableRegistry::getTableLocator();
        /** @var \App\Model\Table\ApplicationformsTable $forms */
        $forms = $applicationforms ?? $locator->get('Applicationforms');
        /** @var \App\Model\Table\ValidationWorkflowRunsTable $workflowRuns */
        $workflowRuns = $runs ?? $locator->get('ValidationWorkflowRuns');
        $this->applicationforms = $forms;
        $this->runs = $workflowRuns;
    }

    /**
     * Archive atomiquement les DAE sélectionnées.
     *
     * @param list<int> $ids Identifiants des demandes ciblées.
     * @return int Nombre de demandes mises à jour.
     */
    public function archive(array $ids): int
    {
        return $this->transition($ids, true);
    }

    /**
     * Restaure atomiquement les DAE sélectionnées.
     *
     * @param list<int> $ids Identifiants des demandes ciblées.
     * @return int Nombre de demandes mises à jour.
     */
    public function restore(array $ids): int
    {
        return $this->transition($ids, false);
    }

    /**
     * Applique une transition explicite après relecture verrouillée des lignes.
     *
     * @param list<int> $ids Identifiants des demandes ciblées.
     * @param bool $archive Vrai pour archiver, faux pour restaurer.
     * @return int Nombre de demandes mises à jour.
     */
    private function transition(array $ids, bool $archive): int
    {
        $ids = array_values(array_unique($ids));
        if ($ids === [] || min($ids) < 1) {
            throw new RuntimeException(__('La sélection de demandes est invalide.'));
        }

        return $this->applicationforms->getConnection()->transactional(function () use ($ids, $archive): int {
            $forms = $this->applicationforms->find()
                ->where(['Applicationforms.' . Applicationform::FIELD_ID . ' IN' => $ids])
                ->epilog('FOR UPDATE')
                ->all()
                ->toList();
            if (count($forms) !== count($ids)) {
                throw new RuntimeException(__('Une demande sélectionnée n’existe plus.'));
            }

            $activeRunIds = [];
            if ($archive) {
                foreach (
                    $this->runs->find()
                        ->select([ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID])
                        ->where([
                            'ValidationWorkflowRuns.' . ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID . ' IN' => $ids,
                            'ValidationWorkflowRuns.' . ValidationWorkflowRun::FIELD_STATE =>
                                ValidationWorkflowRun::STATE_PENDING,
                        ])
                        ->all() as $run
                ) {
                    $activeRunIds[(int)$run->get(ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID)] = true;
                }
            }

            foreach ($forms as $form) {
                $isArchived = $form->get(Applicationform::FIELD_ARCHIVED) !== null;
                if ($isArchived === $archive) {
                    throw new RuntimeException($archive
                        ? __('Une demande sélectionnée est déjà archivée.')
                        : __('Une demande sélectionnée n’est pas archivée.'));
                }
                if ($archive && isset($activeRunIds[(int)$form->get(Applicationform::FIELD_ID)])) {
                    throw new RuntimeException(__('Un cycle de validation actif empêche l’archivage.'));
                }
            }

            $timestamp = DateTime::now();
            foreach ($forms as $form) {
                $form->set(Applicationform::FIELD_ARCHIVED, $archive ? $timestamp : null);
                $this->applicationforms->saveOrFail($form, ['checkRules' => false]);
            }

            return count($forms);
        });
    }
}
