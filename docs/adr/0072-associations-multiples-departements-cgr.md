# ADR 0072 : Associations multiples des départements, utilisateurs et codes CGR

- Date : 2026-10-30
- Statut : Proposé
- Dépendances : ADR 0005, ADR 0006, ADR 0010, ADR 0012, ADR 0041 et ADR 0064

## Contexte

Le schéma historique rattache un seul responsable à un département via
`departments.current_manager_id` et un code CGR à un département via
`cgr_codes.department_id`. Le département conserve en outre un code CGR
principal (`cgr_code_id`) et une valeur textuelle calculée (`default_cgr`).
Ces colonnes empêchent les associations multiples et dupliquent la notion de
CGR par défaut.

## Décision proposée

- Remplacer le responsable unique par la table `department_managers` avec
  une clé primaire composite (`department_id`, `user_id`).
- Remplacer le rattachement direct des CGR par `department_cgr_codes` avec
  une clé primaire composite (`department_id`, `cgr_code_id`).
- Conserver `cgr_codes` comme catalogue global ; l'unicité métier est portée
  par (`type`, `code`).
- Supprimer `current_manager_id`, `cgr_code_id`, `default_cgr` et
  `cgr_codes.department_id` après reprise contrôlée des données.
- Définir les associations ORM Many-to-Many manuellement avec des alias
  sémantiques explicites, conformément à l'ADR 0012.
- Centraliser l'enregistrement et la dissociation des liens dans
  `DepartmentManagementService`, dans une transaction.
- Conserver les contrôles d'autorisation existants pour le département, le
  catalogue CGR et les mutations d'association.

## Migration des données

La migration crée les deux tables de liaison, dédoublonne les couples issus
des anciennes colonnes, journalise le nombre de valeurs `default_cgr` non
vides avant leur abandon, puis supprime les colonnes obsolètes. Les clés
étrangères utilisent `CASCADE` sur les liaisons afin qu'une suppression d'une
entité ne laisse pas de ligne orpheline.

## Conséquences

Le modèle représente zéro, une ou plusieurs associations sans responsable ni
CGR principal. Les consommateurs doivent charger les collections
`Managers` et `CgrCodes` au lieu des propriétés singulières historiques. La
migration est irréversible au niveau des anciennes colonnes et doit être
validée sur la base MySQL dédiée aux tests.

