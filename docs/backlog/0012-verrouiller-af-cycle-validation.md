# 0012 — Verrouiller une AF pendant son cycle de validation

**Statut :** Prêt

**Priorité :** Haute

## Contexte

Une demande de recrutement (AF) peut aujourd'hui être modifiée ou supprimée
selon des droits qui ne tiennent pas suffisamment compte de l'existence d'un
cycle de validation. Le déclenchement d'un cycle doit préserver la cohérence
de la demande examinée et empêcher une suppression involontaire de ses données
de workflow.

Ce ticket remplace la règle d'autorisation d'édition formulée dans le ticket
workflow `0015` : les Admins visibles ne disposent pas d'un droit général
d'édition pendant un cycle. Il ne remet pas en cause la remise à zéro d'un
cycle par un Admin habilité, telle que définie par l'ADR 0056 ; cette action ne
supprime pas l'AF.

## Prompt Codex

Lis `AGENTS.md`, les ADR acceptés 0026, 0054, 0055 et 0056, puis ce ticket.
Vérifie `git status` à la racine et dans `app/` et préserve strictement les
modifications locales étrangères.

Mettre en œuvre le verrouillage d'une AF dès qu'elle possède un
`ValidationWorkflowRun`, sans considération de l'état du cycle :

- le demandeur ne peut alors ni modifier ni supprimer l'AF ;
- la suppression de l'AF est interdite à tous, à l'exception d'un Super Admin ;
- un Super Admin conserve les droits d'édition et de suppression à tout moment ;
- les utilisateurs éligibles au rôle de l'étape actuellement active peuvent
  modifier l'AF, uniquement pendant cette étape. L'éligibilité doit s'appuyer
  sur le même instantané et les mêmes règles que le vote (rôle actif, périmètre,
  échéance et éventuelle suppléance) ;
- aucun rôle, y compris un Admin visible, ne reçoit un droit d'édition général
  pendant le cycle.

Centraliser ces règles dans les Policies et, si nécessaire, dans un
service/résolveur de workflow réutilisable. Ne pas les reproduire dans les
contrôleurs, les templates ou JavaScript. Le serveur doit refuser les accès
directs aux routes et API ; un bouton masqué ne constitue pas une autorisation.
Les messages de refus doivent être explicites et en français.

Faire disparaître les actions « Éditer » et « Supprimer » de la grille Tabulator
et de la fiche lorsqu'elles sont refusées. Alimenter `grid_rights` depuis les
Policies existantes et conserver les fabriques d'actions et `ActionHelper`.

Pour le Super Admin, afficher des confirmations pédagogiques avant toute
modification effective et avant suppression. La confirmation de suppression
doit annoncer que l'AF ainsi que les votes, étapes et exécution de son éventuel
cycle seront définitivement supprimés.

La suppression par Super Admin doit être une unique transaction : supprimer
d'abord les validations liées aux étapes, les étapes, puis l'exécution du
cycle, et enfin l'AF. Réutiliser ou extraire la logique de remise à zéro de
l'ADR 0056, sans la dupliquer. Toute erreur doit annuler l'ensemble de
l'opération ; les données étrangères au cycle restent préservées conformément
à l'ADR 0056.

Si cette évolution constitue une modification architecturale qui n'est pas
couverte par les ADR acceptés, proposer l'ADR séquentiel correspondant plutôt
que de modifier rétroactivement un ADR accepté.

Ne crée ni branche, ni commit, ni pull request sans accord explicite.

## Critères d'acceptation

1. Dès qu'un cycle existe, quel que soit son état, le demandeur est refusé en
   édition et en suppression côté serveur.
2. Seul un Super Admin peut supprimer une AF ayant un cycle ; il peut toujours
   l'éditer.
3. Un utilisateur éligible au rôle de l'étape active peut éditer l'AF ; il est
   refusé dès que son étape n'est plus active. Les autres utilisateurs, y
   compris les Admins visibles, sont refusés.
4. Les actions interdites ne sont pas rendues dans la grille ni dans la fiche,
   tandis que les routes et API restent protégées par Policy et retournent un
   message français explicite en cas de tentative directe.
5. La suppression d'une AF par Super Admin efface atomiquement les validations,
   étapes et exécution de son cycle éventuel, puis l'AF ; un échec ne laisse
   aucun état partiellement supprimé.
6. Les confirmations Super Admin expliquent les implications de l'édition et
   de la suppression, notamment le caractère définitif de cette dernière.
7. Des tests de Policy, d'intégration/service, fonctionnels HTTP/API et de
   droits de grille couvrent les scénarios ci-dessus.

## Vérifications

- make test.unit
- make test.style
- make test.integration
- make test.workflow
- make test.api
- make cs.check
- make stan

## Références

- [ADR 0055 — Workflow de validation des Applicationforms](../adr/0055-workflow-validation-applicationforms.md)
- [ADR 0056 — Remise à zéro exceptionnelle d’un cycle de validation](../adr/0056-remise-a-zero-cycle-validation.md)
- [Ticket workflow 0015 — Édition et audit](validation-applicationform/0015-edition-et-audit.md)
- [Ticket workflow 0018 — Annulation et réinitialisation administrative d’un cycle](validation-applicationform/0018-annulation-et-reinitialisation.md)
