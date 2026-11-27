# Paramétrer les champs réinitialisés à la duplication — plan d’implémentation

> **Pour les agents d’implémentation :** exécuter ce plan tâche par tâche et
> vérifier chaque étape avant de passer à la suivante.

**Objectif :** rendre configurables les six champs métier effacés lors des
futures duplications de DAE, tout en gardant les champs protégés toujours
réinitialisés.

**Architecture :** un service central valide et lit la configuration stockée
dans `workflow_settings` sous `applicationform.duplicate_reset_fields`. La
duplication combine la sélection validée avec sa liste permanente de champs
protégés. L’API dédiée et sa section de l’écran partagé sont accessibles
uniquement aux super-administrateurs; la section présente les six choix dans
une grille Tabulator avec sauvegarde immédiate à chaque cellule.

**Technologies :** CakePHP 5, PHP, JavaScript, Tabulator, PHPUnit et `node:test`.

**Spécification :** `docs/adr/0078-parametrer-champs-reinitialises-duplication.md`
et `docs/backlog/0040-parametrer-champs-reinitialises-duplication.md`.

## Contraintes globales

- Les six champs configurables sont `hr_description`,
  `control_management_validation`, `final_argument`, `budgetfeature_id`,
  `yesno_id` et `qualification`.
- La configuration absente ou invalide retombe sur la liste sûre des six
  champs; une liste vide, inconnue ou protégée est refusée à l’écriture.
- L’identité, l’attribution, le salaire de base, l’état, les votes, les
  commentaires et les données de workflow restent toujours réinitialisés.
- Seules les nouvelles duplications sont concernées; les horodatages suivent
  la sauvegarde habituelle et aucun audit dédié n’est ajouté.
- Les autres réglages de `/workflow-settings` conservent leurs droits actuels.
- Préserver les modifications documentaires déjà présentes et ne pas créer de
  commit sans nouvelle validation explicite de l’opérateur.

---

### Tâche 1 : service de configuration et validation de la liste

**Fichiers :**
- Créer `src/Service/ApplicationformDuplicationSettings.php`.
- Créer `tests/TestCase/Service/ApplicationformDuplicationSettingsTest.php`.

**Interfaces :**
- `ApplicationformDuplicationSettings::CONFIGURABLE_FIELDS` expose les six
  champs et `SETTING_NAME` la clé existante.
- `resolveResetFields(?string $storedValue): array` retourne une liste valide
  ou les six champs sûrs par défaut.
- `validateResetFields(mixed $fields): array` retourne un tableau vide si la
  liste non vide ne contient que des champs configurables, sinon des erreurs
  structurées sous la clé `fields`.
- `serializeResetFields(array $fields): string` conserve le format CSV déjà
  utilisé par la clé historique.

- [x] **Étape 1 :** écrire les tests de configuration par défaut, de valeurs
  valides, de repli sur valeur absente/malformée/vide/inconnue/protégée, et de
  refus des listes soumises vides ou hors liste.
- [x] **Étape 2 :** lancer le test unitaire ciblé et confirmer les échecs dus
  à l’absence du service.
- [x] **Étape 3 :** implémenter les constantes, le parseur sûr, le validateur
  et la sérialisation minimale pour faire passer les tests.
- [x] **Étape 4 :** relancer le test ciblé et vérifier que chaque cas passe.

### Tâche 2 : appliquer la sélection à la duplication

**Fichiers :**
- Modifier `src/Model/Table/ApplicationformsTable.php`.
- Modifier `tests/TestCase/Model/Table/ApplicationformsTableTest.php`.

**Interfaces :**
- La liste existante des champs protégés reste toujours exclue de la copie.
- `duplicateFor()` lit la valeur persistée avec la clé existante, la résout
  via `ApplicationformDuplicationSettings`, puis exclut aussi les champs
  configurés de `DUPLICATION_SOURCE_FIELDS`.

- [x] **Étape 1 :** ajouter des cas qui vérifient la reprise d’un champ métier
  retiré de la sélection et l’effacement d’un champ encore sélectionné.
- [x] **Étape 2 :** ajouter des cas confirmant que les champs protégés restent
  effacés, même avec une valeur historique invalide ou absente.
- [x] **Étape 3 :** lancer les tests de duplication et confirmer les échecs
  attendus avec la liste actuellement codée en dur.
- [x] **Étape 4 :** raccorder la lecture du réglage au calcul des champs
  copiés, sans changer les contrôles de références ni la sauvegarde standard.
- [x] **Étape 5 :** relancer les tests ciblés et confirmer le comportement des
  sélections valide, absente et invalide.

### Tâche 3 : protéger et rendre configurable l’API dédiée

**Fichiers :**
- Modifier `src/Policy/WorkflowSettingPolicy.php`.
- Modifier `src/Controller/Api/WorkflowSettingsController.php`.
- Modifier `tests/TestCase/Policy/WorkflowSettingsPolicyTest.php`.
- Modifier `tests/TestCase/Controller/Api/WorkflowSettingsControllerTest.php`.

**Interfaces :**
- Ajouter les actions `indexDuplicateResetFields` et
  `manageDuplicateResetFields`, limitées au super-administrateur.
- GET renvoie `{success, fields, options}` avec les choix actuels et les six
  champs configurables.
- POST reçoit `fields: string[]`, sauvegarde la liste CSV et renvoie
  `{success, fields}`; les erreurs de validation renvoient
  `{success, message, errors}` avec HTTP 422.
- Les autorisations des autres routes restent inchangées.

- [x] **Étape 1 :** tester que seuls les super-administrateurs peuvent lire et
  modifier le réglage dédié; vérifier séparément les droits génériques des
  autres routes.
- [x] **Étape 2 :** tester le GET avec six options et la valeur sûre par défaut,
  puis le POST valide et les rejets vide/inconnu/protégé avec contrat 422.
- [x] **Étape 3 :** lancer les tests Policy et API et confirmer les échecs
  avant le changement d’ACL et d’endpoint.
- [x] **Étape 4 :** appliquer les deux actions Policy, remplacer le refus
  historique du POST et exposer les options depuis le service central.
- [x] **Étape 5 :** relancer les tests ciblés et confirmer que les autres
  permissions de paramétrage n’ont pas changé.

### Tâche 4 : grille Tabulator dédiée aux six choix

**Fichiers :**
- Modifier `templates/WorkflowSettings/index.php`.
- Modifier `webroot/js/views/WorkflowSettings/index.js`.
- Modifier `webroot/js/views/WorkflowSettings/settings-grid.js`.
- Modifier `tests/js/workflow-settings-grid.test.mjs`.
- Modifier `tests/TestCase/Controller/Api/WorkflowSettingsControllerTest.php`.

**Interfaces :**
- Masquer la section aux comptes non super-administrateurs et ne pas appeler
  son endpoint depuis leur écran.
- Présenter chaque champ comme une ligne, avec une cellule booléenne
  « Réinitialiser à la duplication »; chaque validation POSTe la liste
  complète à `/api/workflow-settings/duplicate-reset-fields.json`.
- Bloquer temporairement les autres éditions de cette grille pendant une
  sauvegarde afin d’éviter deux listes complètes concurrentes.
- En cas d’erreur, restaurer la valeur de cellule et afficher le message API.

- [x] **Étape 1 :** ajouter les tests JS de construction des lignes, du
  contrôle booléen, de la requête avec la liste complète et de la restauration
  en cas d’erreur.
- [x] **Étape 2 :** lancer `rtk node tests/js/workflow-settings-grid.test.mjs`
  et vérifier les échecs attendus avant l’implémentation.
- [x] **Étape 3 :** ajouter la grille locale, son éditeur booléen, la requête
  POST et le verrouillage pendant la requête.
- [x] **Étape 4 :** remplacer le placeholder du template, exposer le droit
  super-administrateur et charger la grille uniquement pour ce droit.
- [x] **Étape 5 :** relancer les tests JS et API, puis vérifier les onglets et
  l’absence d’appel dédié pour les autres comptes.

### Tâche 5 : documentation et validation complète

**Fichiers :**
- Modifier `docs/backlog/0040-parametrer-champs-reinitialises-duplication.md`.
- Modifier `webroot/js/views/README.md` si le nouveau flux ajoute un motif
  Tabulator réutilisable qui n’y figure pas déjà.

- [x] **Étape 1 :** documenter le format persisté, les valeurs par défaut, les
  protections, l’accès super-administrateur et le comportement de repli.
- [x] **Étape 2 :** mettre à jour l’état du ticket après validation seulement.
- [x] **Étape 3 :** exécuter `rtk make -C .. test.unit`,
  `rtk make -C .. test.integration`, `rtk make -C .. test.api`,
  `rtk make -C .. test.style`, `rtk make -C .. cs.check`,
  `rtk make -C .. stan` et `rtk make -C .. test.all`.
- [x] **Étape 4 :** inspecter les changements avec `rtk git diff --check`,
  `rtk git diff` et `rtk git status --short --branch`; relever tout contrôle
  non exécuté ou toute limite de recette.
