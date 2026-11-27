# 0040 — Paramétrer les champs réinitialisés à la duplication

**Statut :** Bonus retenu — à démarrer après le ticket 0043

**Priorité :** Basse — bonus retenu

**Dépendance :** La refonte de la page `/workflow-settings` du ticket 0043
doit être terminée avant d’intégrer ce réglage. Aucune dépendance au ticket
0039 : ce bonus reste indépendant de la séquence 0030–0039 et n'appartient pas
au manifeste obligatoire.

## Objectif

Paramétrer la réinitialisation ou la reprise des six champs identifiés
`hr_description`, `control_management_validation`, `final_argument`,
`budgetfeature_id`, `yesno_id` et `qualification` lors de la duplication d'une
DAE. Le réglage est global et s'applique aux nouvelles duplications uniquement.

## Travail attendu

1. **Cadrer.** Poser séparément la question de vider ou de reprendre la valeur
   pour chacun des six champs dans [Q-0040](../meetings/questions-ouvertes.md).
   Consigner la réponse métier sans l'interpréter. Si une exception locale est
   demandée, la traiter explicitement avant de finaliser le périmètre global.
   Si les six réponses conduisent à reprendre les valeurs et donc à une liste
   de réinitialisation vide, faire résoudre ce cas avec le métier avant le
   code ; la liste vide reste invalide et aucune réponse ne doit être présumée.
2. **Décider.** Soumettre l'[ADR 0078](../adr/0078-parametrer-champs-reinitialises-duplication.md)
   au statut Proposé et le faire relire avant toute implémentation.
3. **Configurer.** Réutiliser `workflow_settings` et la clé existante
   `applicationform.duplicate_reset_fields`. Les six champs configurables
   sont tous réinitialisés par défaut. Exclure de la liste configurable les
   champs protégés (`user_id`, `assistant_id`, identité de la personne,
   `collaborator_id`, `salary_base`, `archived`, `deleted`, ainsi que les
   votes, commentaires et éléments de workflow) et continuer à les
   réinitialiser systématiquement. Refuser les listes vides, les champs
   inconnus et les champs protégés. Si le réglage est absent ou invalide au
   moment de la duplication, appliquer la liste sûre actuelle.
4. **Administrer.** Réutiliser la page `/workflow-settings` et l'endpoint
   existant `/api/workflow-settings/duplicate-reset-fields.json`. GET expose
   le réglage courant et les options configurables ; POST reçoit
   `fields: string[]`. La page étant partagée, ses autres paramètres conservent
   les autorisations existantes `workflowsettings.read` et
   `workflowsettings.update`. Réserver uniquement la section de duplication
   et ses lectures et mutations API dédiées au super-administrateur, avec un
   contrôle serveur y compris lors d'un appel direct. Retourner les erreurs de
   validation avec le contrat API `{success, message, errors}` et HTTP 422
   lorsque le réglage est invalide.
5. **Vérifier et clôturer.** Ajouter les tests, documenter le réglage et les
   résultats, puis clôturer le ticket après validation.

## Critères d'acceptation

1. Les réponses métier aux six questions sont consignées sans interprétation
   et toute exception locale est traitée explicitement. Le cas où les six
   réponses demandent de reprendre les valeurs est résolu avant le code, sans
   accepter une liste vide ni présumer la réponse métier.
2. L'ADR 0078 est proposé et relu avant le début du code.
3. Les six champs sont réinitialisés par défaut ; une sélection valide permet
   de choisir, pour chacun, s'il est vidé ou repris à la duplication.
4. L'identité, l'attribution, l'état, les votes et les données d'audit
   protégés ne peuvent pas être configurés pour être repris et restent
   réinitialisés.
5. Une liste vide, inconnue ou contenant un champ protégé est refusée ; une
   configuration persistée absente ou invalide déclenche le repli sûr.
6. La section de duplication et ses endpoints API dédiés refusent côté serveur
   les comptes non super-administrateurs. Les autres paramètres de la page
   partagée conservent leurs autorisations existantes
   `workflowsettings.read/update`. Les mutations API respectent le contrat
   `{success, message, errors}` et HTTP 422 en cas de validation invalide.
7. Le réglage n'affecte que les duplications futures. Les horodatages
   `created` et `modified` suivent le comportement standard ; aucun journal
   d'audit dédié n'est créé.

## Vérifications

Ajouter des cas couvrant : les six champs configurables et leur réinitialisation
par défaut ; le maintien ou la reprise d'une valeur selon chaque choix ; la
réinitialisation permanente des champs protégés ; le refus des listes vides,
inconnues ou contenant un champ protégé ; le repli sur la liste sûre actuelle
pour une valeur absente ou invalide ; les accès GET et POST autorisés au
super-administrateur et refusés aux autres comptes, sans changement des droits
existants pour les autres paramètres ; l'absence d'effet sur les DAE déjà
créées ; les horodatages usuels et l'absence de journal d'audit dédié.

Exécuter via le Makefile racine, avec la base dédiée `daetf2_test` :

- `make test.unit` ;
- `make test.integration` ;
- `make test.api` ;
- `make test.style` ;
- `make cs.check` ;
- `make stan` ;
- `make test.all`.

## Références

- [0020 — Fiabiliser la duplication des DAE](0020-fiabiliser-la-duplication-des-dae.md)
- [Q-0040 — Choix métier des six champs](../meetings/questions-ouvertes.md)
- [ADR 0078 — Paramétrer les champs réinitialisés à la duplication](../adr/0078-parametrer-champs-reinitialises-duplication.md)
