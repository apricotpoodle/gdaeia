# 0020 — Fiabiliser la duplication des DAE

**Statut :** À planifier

**Priorité :** Haute

## Contexte

La duplication d'une DAE doit produire une nouvelle demande réutilisable,
mais certains champs appartiennent au traitement administratif ou RH de la
demande source et ne doivent pas être reconduits dans la copie. La liste
actuelle est partiellement portée par le paramétrage
`applicationform.duplicate_reset_fields` et doit être confrontée aux champs
de la zone « Champs Réservés Administration/RH ».

Par ailleurs, l'API retourne actuellement un message générique lorsque la
duplication échoue. L'opérateur ne sait donc pas quel champ, quelle
référence ou quelle condition doit être corrigé avant de recommencer.

## Objectif

Sécuriser la duplication d'une DAE en effaçant systématiquement les champs
réservés à l'administration et en rendant chaque blocage explicite et
actionnable.

## Travail attendu

- [ ] Inventorier les champs de la zone « Champs Réservés
  Administration/RH » : `hr_description`,
  `control_management_validation`, `final_argument`, `budgetfeature_id`,
  `yesno_id` et `qualification`.
- [ ] Comparer cet inventaire avec les champs déjà réinitialisés lors d'une
  duplication, notamment `user_id`, `applicantname` et `salary_base`, puis
  définir une source de vérité unique documentée.
- [ ] Effacer dans la copie tous les champs réservés identifiés, ainsi que
  les identifiants, commentaires, visas et éléments de workflow qui ne
  doivent pas être hérités d'une DAE source.
- [ ] Conserver les données métier réutilisables et affecter la nouvelle DAE
  à l'opérateur courant selon les règles existantes.
- [ ] Vérifier que la copie repart dans l'état attendu et ne reprend pas un
  cycle de validation ou une décision administrative de la source.
- [ ] Remplacer le message d'échec générique par un message indiquant la
  cause précise du blocage et l'action corrective attendue.
- [ ] Retourner les erreurs de validation par champ lorsque l'échec vient de
  données obligatoires ou de références invalides.
- [ ] Distinguer les cas suivants dans les messages : DAE non visible ou
  non autorisée, référence supprimée/invalide, champ obligatoire manquant,
  dates incohérentes, duplication impossible à persister et autre règle
  métier bloquante.
- [ ] Couvrir le comportement par des tests unitaires, d'intégration et API.

## Critères d'acceptation

1. Une DAE dupliquée ne contient aucun champ réservé à l'administration ou
   au traitement RH selon la liste de référence validée.
2. La copie conserve uniquement les informations métier prévues et est
   rattachée à l'opérateur courant ; les commentaires, visas et validations
   de la source ne sont pas copiés.
3. Une duplication bloquée par une DAE inaccessible renvoie un message
   indiquant que l'utilisateur doit vérifier son périmètre ou ses droits.
4. Une duplication bloquée par une référence invalide nomme la référence à
   corriger ou à sélectionner de nouveau.
5. Une erreur de validation indique le champ concerné et la correction
   attendue ; le message ne se limite jamais à « vérifiez les champs
   obligatoires ».
6. Une erreur inattendue de persistance est signalée sans exposer de détail
   technique ou de donnée sensible, tout en permettant de distinguer ce cas
   d'une erreur de saisie.
7. Les tests couvrent au moins une duplication réussie et chaque catégorie
   de blocage exposée par l'API.

## Vérifications

Exécuter les commandes adaptées via le Makefile racine :

- `make test.unit` ;
- `make test.integration` pour les règles de copie ORM ;
- `make test.api` pour les réponses de l'API ;
- `make test.style` si les tests sont modifiés ;
- `make cs.check` et `make stan` si du PHP est modifié.

## Références

- `src/Model/Table/ApplicationformsTable.php`
- `src/Controller/Api/ApplicationformsController.php`
- `src/Controller/Api/WorkflowSettingsController.php`
- `templates/element/Applicationforms/zone_reserves.php`
- `tests/TestCase/Controller/Api/ApplicationformsControllerTest.php`
- `tests/TestCase/Model/Table/ApplicationformsTableTest.php`
