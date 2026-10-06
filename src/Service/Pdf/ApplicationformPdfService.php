<?php
declare(strict_types=1);

namespace App\Service\Pdf;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Service\Security\FieldAuthorizationService;
use Authorization\IdentityInterface;
use Cake\I18n\DateInterface;
use Cake\ORM\TableRegistry;
use DateTimeInterface;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/** Produit le PDF sécurisé d'une demande de recrutement. */
final class ApplicationformPdfService
{
    /**
     * Génère un document PDF en appliquant les droits de la fiche et du workflow.
     *
     * @param \App\Model\Entity\Applicationform $applicationform Demande à produire.
     * @param \Authorization\IdentityInterface $identity Opérateur courant.
     * @return string Contenu binaire du PDF.
     */
    public function generate(Applicationform $applicationform, IdentityInterface $identity): string
    {
        $schema = (new FieldAuthorizationService())->getFieldSchema($identity, 'Applicationforms');
        $steps = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->contain(['Roles', 'Validations' => ['Users', 'Validationstatuses']])
            ->where(['Applicationvalidationsteps.applicationform_id' => $applicationform->id])
            ->orderByAsc('Applicationvalidationsteps.sequence_number')
            ->all()
            ->toList();

        $html = $this->renderHtml($applicationform, $schema, $steps, $identity);
        $pdf = new Mpdf([
            'format' => 'A4',
            'orientation' => 'P',
            'tempDir' => TMP,
            'default_font' => 'dejavusans',
        ]);
        $pdf->SetTitle(sprintf('DAE n°%s', $applicationform->id));
        $pdf->SetAuthor('GDAETF2');
        $pdf->WriteHTML($html);

        return $pdf->Output('', Destination::STRING_RETURN);
    }

    /**
     * @param array<string, string> $schema Autorisations de champs existantes.
     * @param list<\App\Model\Entity\Applicationvalidationstep> $steps Étapes du cycle.
     */
    private function renderHtml(
        Applicationform $applicationform,
        array $schema,
        array $steps,
        IdentityInterface $identity,
    ): string {
        $rows = [];
        $fields = [
            'jobtitle' => ['Intitulé du poste', $applicationform->jobtitle],
            'department' => [
                'Service / département',
                $applicationform->department?->name ?? $applicationform->department?->code,
            ],
            'begin_at' => ['Date de début', $this->formatDate($applicationform->begin_at)],
            'end_at' => ['Date de fin', $this->formatDate($applicationform->end_at)],
            'candidate_name' => ['Candidat / collaborateur', $applicationform->candidate_name],
            'contracttype' => [
                'Type de contrat',
                $applicationform->contracttype?->name ?? $applicationform->contracttype?->code,
            ],
            'hiringreason' => ['Motif de recrutement', $applicationform->hiringreason?->name],
            'professionalcategory' => ['Catégorie professionnelle', $applicationform->professionalcategory?->name],
            'worktime' => ['Temps de travail', $applicationform->worktime?->name],
            'cgr' => ['Code CGR', $applicationform->cgr],
            'budgetfeature' => ['Caractéristique budgétaire', $applicationform->budgetfeature?->name],
            'grossremuneration' => ['Rémunération brute', $applicationform->grossremuneration],
            'period' => ['Périodicité', $applicationform->period?->name],
            'qualification' => ['Qualification', $applicationform->qualification],
            'reasonforreplacement' => ['Motif de remplacement', $applicationform->reasonforreplacement],
            'workingtimedistribution' => [
                'Répartition du temps de travail',
                $applicationform->workingtimedistribution,
            ],
        ];
        foreach ($fields as $field => [$label, $value]) {
            if (!$this->isVisible($schema, $field) || $value === null || $value === '') {
                continue;
            }
            $rows[] = sprintf(
                '<tr><th>%s</th><td>%s</td></tr>',
                $this->escape($label),
                $this->escape((string)$value),
            );
        }

        $workflowRows = array_map(
            fn($step): string => $this->workflowRow($step, $identity, $applicationform),
            $steps,
        );

        return '<!doctype html><html lang="fr"><head><meta charset="utf-8"><style>'
            . 'body{font-family:dejavusans,sans-serif;color:#263238;font-size:10pt}'
            . 'h1{color:#0d47a1;font-size:19pt;margin-bottom:4px}'
            . 'h2{color:#1565c0;font-size:13pt;border-bottom:1px solid #90caf9;'
            . 'padding-bottom:4px;margin-top:20px}'
            . '.meta{color:#546e7a;margin-bottom:18px}'
            . '.data{width:100%;border-collapse:collapse}'
            . '.data th{width:34%;text-align:left;background:#eef4fb}'
            . '.data th,.data td{border:1px solid #cfd8dc;padding:6px}'
            . '.workflow{width:100%;border-collapse:collapse}'
            . '.workflow th{background:#1565c0;color:#fff}'
            . '.workflow th,.workflow td{border:1px solid #cfd8dc;padding:5px}'
            . '.state{font-weight:bold}.accepted{color:#2e7d32}'
            . '.rejected{color:#c62828}.pending{color:#ef6c00}'
            . '</style></head><body>'
            . sprintf('<h1>Demande n°%s</h1>', $this->escape((string)$applicationform->id))
            . '<div class="meta">Synthèse générée le ' . $this->escape(date('d/m/Y à H:i')) . '</div>'
            . '<h2>Informations de la demande</h2><table class="data">' . implode('', $rows) . '</table>'
            . '<h2>Cycle de validation</h2><table class="workflow"><thead><tr>'
            . '<th>Étape</th><th>Rôle</th><th>Opérateur</th><th>Statut</th>'
            . '<th>Date</th><th>Commentaire final</th></tr></thead><tbody>'
            . implode('', $workflowRows)
            . '</tbody></table></body></html>';
    }

    /** @param array<string, string> $schema */
    private function isVisible(array $schema, string $field): bool
    {
        return !isset($schema[$field]) || strtoupper($schema[$field]) !== 'NONE';
    }

    /** Construit une ligne du tableau du cycle de validation. */
    private function workflowRow(
        Applicationvalidationstep $step,
        IdentityInterface $identity,
        Applicationform $applicationform,
    ): string {
        $state = (string)($step->state ?? 'en_attente');
        $status = match ($state) {
            'acceptee' => ['Accepté', 'accepted'],
            'refusee' => ['Refusé', 'rejected'],
            default => ['En attente', 'pending'],
        };
        $validation = $step->validation ?? null;
        $operator = $validation?->user?->display_name ?? $validation?->user?->email ?? '-';
        $date = $validation?->validated ?? $step->completed_at;
        $comment = $identity->can('viewZoneCommentaires', $applicationform) ? ($validation?->obs ?? '') : '';

        return sprintf(
            '<tr><td>%s</td><td>%s</td><td>%s</td><td class="state %s">%s</td><td>%s</td><td>%s</td></tr>',
            $this->escape((string)($step->sequence_number ?? '-')),
            $this->escape((string)($step->role?->name ?? '-')),
            $this->escape((string)$operator),
            $status[1],
            $status[0],
            $this->escape($this->formatDate($date)),
            $this->escape((string)$comment),
        );
    }

    /** Formate une date selon la présentation française du document. */
    private function formatDate(mixed $date): string
    {
        if ($date instanceof DateInterface) {
            return $date->format('d/m/Y');
        }
        if ($date instanceof DateTimeInterface) {
            return $date->format('d/m/Y');
        }

        return $date === null ? '-' : (string)$date;
    }

    /** Échappe une valeur avant son insertion dans le gabarit HTML. */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
