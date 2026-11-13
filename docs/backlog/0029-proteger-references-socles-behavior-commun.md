# 0029 — Protéger les références socles avec un Behavior commun

**Statut :** À planifier

**Priorité :** Haute — intégrité des nomenclatures

## Contexte

Le champ booléen `base` désigne les enregistrements fournis par le socle
applicatif. Les contrôles Web/API ne couvrent pas les écritures ORM internes.
Une règle commune doit protéger ces lignes sans faire dépendre l'ORM de
l'identité de l'opérateur.

Les neuf Tables concernées exposent `base`, `code`, `name` et `sort` :
`budgetfeatures`, `contracttypes`, `hiringreasons`, `periods`,
`professionalcategories`, `worktimes`, `yesnos`, `departments` et
`roles`. Les deux dernières gardent leurs Tables, Policies et parcours
d'administration spécialisés. La présence des colonnes ne déclenche jamais
automatiquement la protection.

## Règles métier

- `id` est immuable pour toutes les lignes.
- Une ligne peut être créée avec `base = true` et ses valeurs initiales. Dans
  les parcours Web/API, seul le superadministrateur peut renseigner `base`.
- Si la valeur persistée **ou demandée** de `base` est vraie, une mise à jour
  ne peut changer que `base`, seul. Les basculements `false` → `true` et
  `true` → `false` ne peuvent accompagner aucun autre champ ni aucune
  association.
- `code` ne change pas tant qu'une ligne est ou devient socle. Une ligne qui
  reste `base = true` ne peut modifier ni `code`, ni `name`, ni `sort`, ni
  aucun autre champ. Un changement de ces données exige de retirer `base`
  dans une opération distincte.
- Une ligne persistée `base = true` ne peut être supprimée physiquement ou
  logiquement, même par le superadministrateur. La décision repose sur la
  valeur persistée, pas sur un basculement non sauvegardé.
- Les lignes locales conservent leur CRUD actuel, sous réserve de
  l'immuabilité de `id` et du basculement isolé de `base`.

## Travail attendu

### Architecture et ORM

- [x] Inventorier les neuf Tables correspondant au contrat structurel.
- [ ] Créer `ProtectBaseRecordsBehavior` pour appliquer les règles aux
  opérations ORM standard.
- [ ] Créer `ReferenceTable extends AppTable` et faire hériter les sept
  nomenclatures communes de cette classe. Attacher le Behavior explicitement
  à `DepartmentsTable` et `RolesTable`.
- [ ] Garder l'identité hors du Behavior : les Policies et
  `FieldAuthorizationService` réservent la mutation de `base` au
  superadministrateur dans les parcours Web/API. Sur un département socle,
  seul le superadministrateur peut accéder à l'édition.
- [ ] Refuser avant persistance toute modification d'association de
  département accompagnant un basculement de `base`.

### Interface, API et documentation

- [ ] Ajouter `base` aux formulaires de création et d'édition des références
  communes et des rôles pour le superadministrateur ; conserver le contrôle
  existant du formulaire des départements. Les autres champs d'une ligne
  socle restent en lecture seule. Aucune nouvelle colonne Tabulator n'est
  prévue.
- [ ] Exposer `base` dans les réponses API et filtrer les requêtes avant
  `patchEntity()`. Une requête non autorisée ne doit pas pouvoir imposer ce
  champ. Les refus de validation utilisent `{success, message, errors}` et
  HTTP 422.
- [x] Rédiger l'ADR applicatif 0075 au statut `Proposé`, actualiser son
  index et les README des couches concernées.
- [x] Documenter que `updateAll`, `deleteAll` et le SQL direct contournent
  les callbacks ORM. Aucun schéma, migration ou trigger n'est prévu.

## Critères d'acceptation

1. Les neuf Tables activent explicitement le Behavior, par héritage ou
   attachement direct ; `AppTable` ne détecte pas les colonnes.
2. La création d'une ligne socle et les deux basculements isolés de `base`
   fonctionnent pour le superadministrateur ; les autres utilisateurs ne
   peuvent pas soumettre ce champ via Web/API.
3. `id` reste immuable ; `code` ne change pas sur une ligne socle ou lors
   de son activation. Aucune autre donnée ne change avec `base`.
4. Une ligne persistée socle résiste aux suppressions ORM physiques et
   logiques. Une ligne locale conserve son CRUD.
5. Les associations d'un département ne changent pas dans la requête qui
   bascule `base`.
6. Les refus de mutation Web/API présentent les erreurs de validation en
   français, avec HTTP 422 pour l'API.
7. Les tests ORM utilisent exclusivement `daetf2_test` sous MySQL ; les
   tests Web/API couvrent autorisation, formulaires et requêtes forgées sur
   les neuf Tables.

## Vérifications attendues

- `make test.unit`
- `make test.integration`
- `make test.api`
- `make test.style`
- `make cs.check`
- `make stan`
- `make test.all`

## Références

- [ADR 0005 — Modèle de données](../adr/0005-modele-de-donnees.md)
- [ADR 0042 — ACL de champ](../adr/0042-acl-granulaire-niveau-champ-formulaire.md)
- [ADR 0065 — ACL automatique des mutations Web et API](../adr/0065-acl-automatique-des-mutations-web-api.md)
- [ADR 0075 — Protection des références socles](../adr/0075-protection-references-socles.md)
- `src/Model/Entity/Period.php` et `src/Service/SalaryCalculationService.php` :
  les codes des périodes interviennent dans le calcul.
- `src/Policy/ApplicationformPolicy.php` : les codes des contrats interviennent
  dans une règle d'autorisation.
