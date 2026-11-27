<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Applicationform;

/** Valide et résout les champs métier configurables à la duplication d’une DAE. */
final class ApplicationformDuplicationSettings
{
    public const SETTING_NAME = 'applicationform.duplicate_reset_fields';

    /** @var array<string> */
    public const CONFIGURABLE_FIELDS = [
        Applicationform::FIELD_HR_DESCRIPTION,
        Applicationform::FIELD_CONTROL_MANAGEMENT_VALIDATION,
        Applicationform::FIELD_FINAL_ARGUMENT,
        Applicationform::FIELD_BUDGETFEATURE_ID,
        Applicationform::FIELD_YESNO_ID,
        Applicationform::FIELD_QUALIFICATION,
    ];

    /**
     * Retourne la sélection persistée ou la liste sûre complète si elle est invalide.
     *
     * @param string|null $storedValue Valeur CSV de workflow_settings.
     * @return array<string> Champs à réinitialiser.
     */
    public static function resolveResetFields(?string $storedValue): array
    {
        if ($storedValue === null) {
            return self::CONFIGURABLE_FIELDS;
        }

        $fields = array_map('trim', explode(',', $storedValue));
        if (self::validateResetFields($fields) !== []) {
            return self::CONFIGURABLE_FIELDS;
        }

        return array_values(array_unique($fields));
    }

    /**
     * Contrôle une liste reçue par API et retourne ses erreurs par champ.
     *
     * @param mixed $fields Liste de champs soumise.
     * @return array<string, array<string>> Erreurs de validation.
     */
    public static function validateResetFields(mixed $fields): array
    {
        if (!is_array($fields) || !array_is_list($fields) || $fields === []) {
            return ['fields' => [__('Sélectionnez au moins un champ à réinitialiser.')]];
        }

        foreach ($fields as $field) {
            if (!is_string($field) || !in_array($field, self::CONFIGURABLE_FIELDS, true)) {
                return ['fields' => [__('La liste contient un champ inconnu ou protégé.')]];
            }
        }

        return [];
    }

    /**
     * Sérialise une sélection validée dans le format CSV historique.
     *
     * @param array<string> $fields Champs configurables à réinitialiser.
     * @return string Valeur persistée.
     */
    public static function serializeResetFields(array $fields): string
    {
        return implode(',', array_values(array_unique($fields)));
    }

    /** @return array<array{field: string, label: string}> Champs configurables et libellés affichés. */
    public static function fieldOptions(): array
    {
        $labels = [
            Applicationform::FIELD_HR_DESCRIPTION => __('Description RH'),
            Applicationform::FIELD_CONTROL_MANAGEMENT_VALIDATION => __('Validation du Contrôle de gestion'),
            Applicationform::FIELD_FINAL_ARGUMENT => __('Argumentaire final'),
            Applicationform::FIELD_BUDGETFEATURE_ID => __('Fonction budgétaire'),
            Applicationform::FIELD_YESNO_ID => __('Réponse oui/non'),
            Applicationform::FIELD_QUALIFICATION => __('Qualification'),
        ];

        $options = [];
        foreach (self::CONFIGURABLE_FIELDS as $field) {
            $options[] = ['field' => $field, 'label' => $labels[$field]];
        }

        return $options;
    }
}
