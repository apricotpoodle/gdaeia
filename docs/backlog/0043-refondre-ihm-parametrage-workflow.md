# 0043 — Repenser l’IHM du paramétrage du workflow

**Statut :** À planifier

**Priorité :** Haute — préalable à l’intégration du réglage de duplication

**Dépendance :** Aucune. Le ticket 0040 dépend de cette refonte pour intégrer
son réglage dans la page réorganisée ; il reste indépendant de la séquence
0030–0039.

## Contexte

La page `/workflow-settings` rassemble actuellement plusieurs paramètres
distincts dans une longue page : coefficient de conversion, délais de
validation, détection des cycles bloqués, obligation de commentaire et
catalogue de commentaires prédéfinis. Le ticket 0040 ajoutera le choix des
champs réinitialisés lors de la duplication d’une DAE et augmenterait encore
la densité de cet écran.

Les valeurs simples sont enregistrées par des formulaires et appels API
séparés. Le projet fournit déjà un socle Tabulator pour l’édition inline et la
sauvegarde d’une ligne après validation d’une cellule.

## Objectif

Réorganiser `/workflow-settings` en onglets et présenter les paramètres
simples dans une grille Tabulator où chaque ligne choisit l’éditeur adapté à
son type. Toute cellule modifiée est enregistrée dès validation, avec un
retour visible en cas de succès ou d’erreur.

La refonte conserve les règles métier, les contrats API et les autorisations
existantes. Elle prépare la place du réglage de duplication qui sera livré
séparément par le ticket 0040.

## Travail attendu

1. **Cadrer les données existantes.** Recenser les réglages, leurs API,
   validations et autorisations avant de remplacer leurs formulaires actuels.
2. **Réorganiser la page.** Présenter les réglages dans trois onglets :
   - **Validation** : délai global, détection des cycles bloqués et obligation
     de commentaire lors d’un vote ;
   - **Commentaires** : catalogue CRUD des commentaires prédéfinis ;
   - **Paramètres des DAE** : coefficient de conversion et emplacement du
     futur réglage de duplication du ticket 0040.
3. **Créer la grille des paramètres simples.** Utiliser des lignes décrivant
   le libellé, une aide ou une unité, la valeur et le type de valeur. La
   colonne de valeur sélectionne son éditeur d’après le type de la ligne :
   numérique, booléen, date ou texte. Les réglages actuels utilisent les
   éditeurs numériques et booléens ; les types date et texte sont disponibles
   pour les paramètres qui en ont besoin.
4. **Enregistrer les cellules modifiées.** Envoyer la mutation immédiatement
   après validation de la cellule. Pour un booléen, la validation suit le
   basculement ; pour les autres valeurs, elle suit la confirmation ou la
   sortie de l’éditeur. En cas d’échec, restaurer l’ancienne valeur et afficher
   le message d’erreur.
5. **Préserver les contrôles existants.** Continuer d’utiliser les endpoints
   et validations adaptés à chaque réglage. Ne pas remplacer leurs
   autorisations par un accès général à la grille. Le réglage du ticket 0040
   reste réservé au super-administrateur côté serveur.
6. **Vérifier et documenter.** Couvrir les éditeurs et la sauvegarde inline,
   vérifier l’affichage responsive et au clavier, puis mettre à jour la
   documentation concernée.

## Critères d’acceptation

1. Les réglages sont répartis dans les onglets « Validation »,
   « Commentaires » et « Paramètres des DAE », avec un intitulé et un contenu
   compréhensibles.
2. Les paramètres numériques et booléens existants sont affichés dans une
   grille Tabulator avec une unité ou une courte aide lorsque nécessaire.
3. La colonne « Valeur » choisit l’éditeur depuis le type de la ligne et peut
   prendre en charge un nombre, un booléen, une date ou une chaîne de
   caractères.
4. Chaque modification est enregistrée dès validation de la cellule. Le
   succès est signalé ; une erreur serveur restaure l’ancienne valeur et
   affiche un message compréhensible.
5. Les validations serveur, contrats API et autorisations des réglages
   existants restent appliqués. Les accès non autorisés sont refusés côté
   serveur, y compris par appel direct d’un endpoint.
6. Le catalogue conserve ses opérations de création, modification et
   suppression dans l’onglet « Commentaires ».
7. L’onglet « Paramètres des DAE » permet au ticket 0040 d’ajouter les choix de
   duplication sans restructurer de nouveau la page. Les six choix du 0040
   gardent leur validation et leur accès super-administrateur côté serveur.
8. Les onglets et la grille restent utilisables au clavier et sur les largeurs
   d’écran prises en charge par l’application.

## Vérifications

- Ajouter des tests couvrant les éditeurs numériques et booléens utilisés par
  les paramètres existants, la validation d’une cellule, la sauvegarde,
  l’affichage du retour d’erreur et la restauration de l’ancienne valeur.
- Vérifier les permissions par réglage, y compris le refus côté serveur pour
  le réglage de duplication réservé au super-administrateur.
- Vérifier le maintien des opérations CRUD du catalogue de commentaires.
- Vérifier l’usage clavier et l’affichage responsive des onglets et grilles.
- Exécuter les suites adaptées aux composants Web/API modifiés, ainsi que
  `make test.style` si les tests sont modifiés.

## Références

- [0040 — Paramétrer les champs réinitialisés à la duplication](0040-parametrer-champs-reinitialises-duplication.md)
- [ADR 0004 — API Tabulator et patrons de conception](../adr/0004-api-tabulator-et-design-patterns.md)
- [ADR 0014 — Fonctionnalités des grilles Tabulator](../adr/0014-standardisation-fonctionnalites-tabulator.md)
- `templates/WorkflowSettings/index.php`
- `webroot/js/views/WorkflowSettings/index.js`
- `webroot/js/core/Tabulator/TabulatorCellEditHandler.js`
