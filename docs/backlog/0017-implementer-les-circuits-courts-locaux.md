# 0017 — Implémenter les circuits courts locaux de validation

**Statut :** À planifier

**Priorité :** Moyenne

## Contexte

L'ADR 0070 prévoit une fin de circuit facultative selon le type de contrat et
le département exact de la DAE. GDAETF reste multi-sociétés : les exceptions
issues du cahier des charges de la rédaction de la SEM ne doivent pas modifier
les circuits des autres départements.

| Département de la DAE | Type de contrat | Fin du circuit |
|---|---|---|
| Service configuré de la rédaction SEM | Facture | Séquence du SGR incluse |
| Service configuré de la rédaction SEM | Pige | Séquence du Contrôle de gestion incluse |
| Autre couple département–type | Tout contrat | Circuit départemental complet |

Les noms des contrats, départements et rôles désignent la configuration
initiale. Aucune comparaison à ces libellés ou à des identifiants fixes ne
doit être inscrite dans le service de workflow.

## Travail attendu

### Modèle et administration

- [ ] Ajouter un paramétrage de fin de circuit associant un département, un
  type de contrat et un rôle final, avec unicité du couple département–type et
  références contrôlées. L'absence de règle signifie parcours complet.
- [ ] Ajouter une habilitation de gestion par rôle et département, distincte
  du rôle final de validation. Seuls les super-administrateurs désignent les
  rôles gestionnaires. Un gestionnaire agit uniquement sur les départements
  explicitement rattachés à son compte dans `user_departments` et où son rôle
  est habilité ; le rôle Admin global n'est pas requis.
- [ ] Créer la page `/validation-circuit-rules`, accessible depuis
  `/validationsequences`, avec liste, création, modification, retrait et aperçu
  du parcours résultant. L'ordre des rôles et les délais restent administrés
  dans `/validationsequences` ; les paramètres globaux restent dans
  `/workflow-settings`.
- [ ] Permettre la sélection groupée de services dans l'arbre. Enregistrer
  explicitement les départements actuellement sélectionnés, sans extension
  implicite aux futurs descendants ni aux autres branches de la SEM.
- [ ] Protéger la page, les listes, les mutations API et chaque département
  d'une opération groupée par une Policy serveur. Réutiliser les abstractions
  Tabulator, les fabriques d'actions et les scripts de page existants.
- [ ] Afficher les incohérences de configuration : rôle final inactif ou
  absent d'un circuit départemental, rôles parallèles de la dernière séquence
  et absence de validateur éligible.

### Lancement et déroulement d'une DAE

- [ ] Au lancement, chercher la règle par `contracttype_id` et
  `department_id` exacts. Sans règle, copier le circuit départemental complet.
- [ ] Avec une règle, trouver le numéro de séquence du rôle final et copier
  toutes les affectations de numéro inférieur ou égal, y compris tous les
  rôles partageant la dernière séquence. Ne contrôler l'éligibilité des
  validateurs que pour les étapes retenues.
- [ ] Si une règle explicite vise un rôle final absent ou inactif, bloquer le
  lancement avec un message nommant le contrat, le département et le rôle à
  corriger, sans retour silencieux au circuit complet. Bloquer aussi si une
  étape retenue n'a aucun validateur éligible et éviter tout cycle partiel.
- [ ] Conserver la logique de vote : tous les rôles de la dernière séquence
  retenue doivent accepter pour que le cycle devienne `acceptee` ; un refus
  conserve son effet terminal. Les rôles situés après cette séquence ne sont
  ni sollicités ni notifiés.
- [ ] Verrouiller côté serveur le type de contrat et le département tant
  qu'un cycle existe, même après sa clôture. La remise à zéro exceptionnelle
  déjà autorisée permet ensuite un nouveau lancement.
- [ ] Préserver l'instantané des cycles déjà lancés lorsque les règles ou les
  séquences sont modifiées.

### Périmètres des validateurs et configuration initiale

- [ ] Vérifier les séquences et les rattachements `user_departments` des seuls
  services concernés de l'opérateur (en principe un administrateur de la rédaction SEM).
L'opérateur doit pouvoir sélectionner tout ou partie de "ses" departments.
  Y saisir Facture → SGR et Pige → Contrôle de gestion.
- [ ] Conserver l'éligibilité existante : le Contrôle de gestion intervient
  dans un département si son rôle figure dans la séquence de ce département
  et si un utilisateur de ce rôle y est explicitement rattaché. Son éventuel
  rattachement à VM ne crée aucune règle courte pour les DAE de VM. Aucun
  droit de vote global implicite n'est ajouté.
- [ ] Ajouter explicitement une règle pour tout nouveau department auquel
  l'exception devra s'appliquer.

## Critères d'acceptation

1. Facture et Pige des services configurés de la rédaction SEM terminent
   respectivement au SGR et au Contrôle de gestion. Les mêmes contrats hors
   de ce périmètre et les autres contrats sans règle suivent leur circuit
   départemental complet.
2. Tous les rôles partageant la dernière séquence doivent accepter avant que
   la DAE soit acceptée. Aucune étape ultérieure n'est copiée ou notifiée.
3. Le rôle final absent ou inactif, ou une étape retenue sans validateur,
   produit une erreur explicite sans cycle partiellement créé.
4. Les super-administrateurs et les gestionnaires locaux ont exactement les
   accès prévus, y compris via l'API et les opérations groupées. Un SGR hors
   du périmètre sélectionné ne peut pas modifier ses règles.
5. Les cycles préexistants restent inchangés, le type de contrat est
   verrouillé après lancement et la remise à zéro autorisée fonctionne encore.

## Vérifications

Ajouter les tests unitaires de Policy et de service, les tests d'intégration
ORM et les tests fonctionnels HTTP/API nécessaires. Exécuter par le Makefile
racine :

- `make test.unit`
- `make cs.check`
- `make stan`
- `make test.style` si des tests sont modifiés
- `make test.integration` pour les Models et migrations
- `make test.api` pour les contrôleurs Web et API
- `make test.workflow` si les vues SQL ou le workflow sont modifiés
- `make test.all` si le risque transversal le justifie

Les tests utilisant MySQL doivent cibler explicitement `daetf2_test` via
`DATABASE_TEST_URL`. Mettre à jour les README locaux pertinents et vérifier
l'absence de secrets ou de fichiers générés dans le diff.

## Hors périmètre

Les autres demandes du cahier des charges, notamment le bouton « Archiver »,
la récupération d'une AE et les notifications particulières, ne sont pas
implémentées par ce ticket. L'ADR 0069 sur le modèle général d'habilitations
est encore proposé ; ce ticket n'en anticipe pas l'application globale.

## Références

- [ADR 0070 — Circuits courts locaux selon le type de contrat](../adr/0070-circuits-courts-locaux-par-contrat.md)
- [ADR 0055 — Workflow de validation des Applicationforms](../adr/0055-workflow-validation-applicationforms.md)
- [ADR 0050 — Périmètres hiérarchiques explicites](../adr/0050-semantique-perimetres-hierarchiques-utilisateur.md)
- [ADR 0058 — Séparation du paramétrage global](../adr/0058-separation-parametrage-global-workflow.md)
