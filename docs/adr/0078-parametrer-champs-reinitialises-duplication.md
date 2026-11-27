# ADR 0078 : Paramétrer les champs réinitialisés à la duplication

**Date :** 27 novembre 2026

**Statut :** Proposé

**Dépendances :** [ADR 0052](./0052-presentation-unifiee-erreurs-validation-web-api.md), [ADR 0058](./0058-separation-parametrage-global-workflow.md), [ADR 0065](./0065-acl-automatique-des-mutations-web-api.md), [ADR 0069](./0069-habilitations-par-roles-et-perimetres.md)

## Contexte

Le [ticket 0020](../backlog/0020-fiabiliser-la-duplication-des-dae.md) a
établi une liste canonique codée dans
`ApplicationformsTable::DUPLICATION_RESET_FIELDS`. Les six champs
`hr_description`, `control_management_validation`, `final_argument`,
`budgetfeature_id`, `yesno_id` et `qualification` sont actuellement
réinitialisés lors de chaque duplication. L'ancienne clé
`applicationform.duplicate_reset_fields` et son endpoint existent, mais ne
permettent plus de modifier cette liste.

Le besoin retenu est de rendre le choix de réinitialisation de ces six champs
configurable globalement. Cette configuration ne doit pas permettre de
reprendre l'identité, l'attribution, l'état du dossier ou les données de
workflow et d'audit qui sont toujours effacées à la duplication.

## Décision proposée

1. Stocker le réglage global dans `workflow_settings` sous la clé existante
   `applicationform.duplicate_reset_fields`, sans introduire un nouveau
   mécanisme de configuration.
2. Limiter les champs configurables à `hr_description`,
   `control_management_validation`, `final_argument`, `budgetfeature_id`,
   `yesno_id` et `qualification`. Par défaut, les six champs font partie de la
   liste à réinitialiser.
3. Garder les champs protégés hors de cette liste configurable et les
   réinitialiser systématiquement. Cela couvre notamment l'identité et
   l'attribution, les états d'archivage et de suppression, ainsi que les
   votes, commentaires et éléments du workflow ou de l'audit. Les champs
   `user_id`, `assistant_id`, `applicantname`, `applicant_first_name`,
   `applicant_last_name`, `collaborator_id`, `salary_base`, `archived` et
   `deleted` restent également réinitialisés. La configuration ne peut ni
   retirer ces données de la réinitialisation ni les autoriser à être repris.
4. Refuser à l'enregistrement toute liste vide, toute valeur inconnue et tout
   champ protégé. Lors de la duplication, si le réglage est absent ou invalide,
   utiliser la liste sûre actuelle de champs réinitialisés. Le repli protège
   aussi les duplications lorsque des données historiques de configuration
   sont invalides.
5. Réutiliser l'écran `/workflow-settings` et l'endpoint
   `/api/workflow-settings/duplicate-reset-fields.json`. GET expose le réglage
   courant et les six options ; POST reçoit `fields: string[]`. La page est
   partagée avec les autres réglages, qui conservent les autorisations
   `workflowsettings.read` et `workflowsettings.update` de l'ADR 0069. Seule
   la section de duplication et ses endpoints GET/POST dédiés sont réservés au
   super-administrateur, avec un contrôle serveur même lors d'un appel API
   direct. Les autorisations des autres paramètres ne sont pas modifiées.
6. Retourner les erreurs de validation API selon le contrat
   `{success, message, errors}` et utiliser HTTP 422 pour une liste refusée.
7. Appliquer le réglage uniquement aux nouvelles duplications. Les DAE déjà
   créées ne sont pas modifiées. Les champs `created` et `modified` suivent le
   comportement standard ; aucun journal d'audit dédié n'est ajouté.
8. Faire relire le présent ADR au statut **Proposé** avant l'implémentation.
   Toute modification du périmètre décidée lors de cette revue doit être
   consignée avant le début du code.

## Justification

Le stockage dans `workflow_settings` et la page existante maintiennent le
paramétrage au même endroit que les autres réglages globaux du workflow,
conformément à l'ADR 0058. Limiter la sélection aux six champs métier répond
au besoin sans rendre modifiables les protections établies par le ticket 0020.

Le rejet des valeurs invalides empêche d'enregistrer une configuration
ambiguë, tandis que le repli sûr préserve le comportement de protection en
présence d'une donnée absente ou historique. Le contrôle serveur dédié à la
section de duplication et à ses routes API empêche de contourner la restriction
par un appel direct, sans modifier les autorisations des autres réglages de la
page partagée. Le contrat d'erreur reste cohérent avec l'ADR 0052.

## Conséquences

### Positives

* Le super-administrateur peut ajuster un réglage global sans déploiement.
* Les six choix sont modifiables sans exposer les champs d'identité, d'état,
  de workflow ou d'audit.
* Une configuration absente ou invalide conserve le comportement sûr actuel.
* Les duplications déjà effectuées et leurs horodatages ne sont pas modifiés.

### Négatives

* Le traitement de duplication doit lire et valider un paramètre global.
* L'administration doit présenter explicitement les six choix et leurs erreurs
  de validation.
* Les réponses métier à Q-0040 restent nécessaires pour confirmer le choix
  applicable à chaque champ ; le défaut initial est la réinitialisation des
  six champs.
