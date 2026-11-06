# 0028 — Améliorer l'IHM des autorisations de champs

**Statut :** Terminé — implémenté sur `feature/tickets-0028-0027`

**Priorité :** Haute — fiabilité du paramétrage des habilitations

**Prérequis pour :** Le ticket [0027 — Autoriser la création d'une
Applicationform par un demandeur](0027-autoriser-creation-applicationform-demandeur.md)
doit être réalisé après celui-ci, afin d'utiliser le modèle final des
autorisations de champs et de zones.

## Contexte

L'URL `/field-authorizations/add` propose actuellement une saisie libre pour
les champs `resource` et `field`. Cette saisie permet d'enregistrer des
valeurs mal orthographiées, incohérentes avec les ressources ORM ou absentes
du référentiel des champs métier. Certaines valeurs existantes, comme
`job_section` ou `rh_section`, désignent des zones fonctionnelles de l'IHM et
non des colonnes de table.

Le rôle applicatif est déjà sélectionné dans une liste exhaustive. En
revanche, les ressources et les champs doivent être choisis dans un
référentiel contrôlé. La table `field_definitions` contient les ressources,
noms techniques, libellés, descriptions, états d'activation et ordre
d'affichage des champs réels. Elle ne doit pas être utilisée pour stocker les
zones fonctionnelles.

## Objectif

Rendre le paramétrage d'une autorisation fiable et compréhensible, en
distinguant les champs ORM des zones fonctionnelles et en guidant l'opérateur
vers une cible active, sans supprimer la validation serveur ni la protection
des doublons.

## Décisions fonctionnelles

- `field_definitions` reste la source de vérité des champs ORM proposés dans
  l'IHM.
- Un nouveau référentiel `authorization_targets` devient la source de vérité
  des cibles fonctionnelles qui ne sont pas des colonnes, notamment les zones
  `job_section` et `rh_section`.
- `field_authorizations` distingue explicitement les cibles par un type :
  `field` pour un champ ORM ou `section` pour une zone fonctionnelle.
- La liste **Ressource** contient les ressources actives disponibles, sans
  doublon.
- Après le choix du type de cible et de la ressource, la liste **Cible** est
  filtrée sur le référentiel correspondant.
- Chaque option présente son libellé métier et son nom technique, par exemple
  `Intitulé du poste — jobtitle` ou `Poste — job_section`.
- Les listes sont recherchables et affichent la description de la cible choisie
  lorsque celle-ci existe.
- Le même composant et les mêmes règles sont utilisés par `/add` et `/edit`.
- Le serveur refuse toute cible inexistante ou inactive dans le référentiel
  correspondant, y compris si la requête est fabriquée manuellement.
- L'unicité devient `(role_id, resource, target_type, target)`.
- La gestion des autorisations de champs reste réservée au super-administrateur.

## Travail attendu

### Modèle, référentiels et API

- [ ] Créer `authorization_targets` avec la ressource, le type de cible, le
  code, le libellé, la description, l'état actif et l'ordre d'affichage.
- [ ] Déclarer les zones fonctionnelles existantes, notamment `job_section` et
  `rh_section`, dans ce référentiel.
- [ ] Faire évoluer `field_authorizations` pour distinguer `field` et
  `section`, en conservant une migration contrôlée des valeurs historiques.
- [ ] Exposer une API de consultation des ressources et cibles actives issues
  des deux référentiels, avec une réponse exploitable par l'IHM.
- [ ] Permettre le chargement des cibles après sélection du type et de la
  ressource, sans exposer les cibles inactives.
- [ ] Conserver les libellés, descriptions et noms techniques dans la
  réponse afin que l'IHM n'ait pas à les reconstruire.
- [ ] Ajouter une validation ORM côté `FieldAuthorizationsTable` pour vérifier
  l'existence et l'activation de la cible dans le référentiel correspondant.
- [ ] Prévoir le comportement des anciennes autorisations dont la définition
  serait devenue inactive : elles restent consultables et éditables, mais ne
  peuvent pas être sélectionnées pour une nouvelle règle.

### Interface Web

- [ ] Remplacer la saisie texte de `resource` par une liste recherchable.
- [ ] Ajouter un choix explicite du type de cible : champ ORM ou zone
  fonctionnelle.
- [ ] Remplacer la saisie texte de la cible par une liste recherchable,
  dépendante du type et de la ressource.
- [ ] Réinitialiser la cible lorsque le type ou la ressource change et
  empêcher l'envoi d'une cible appartenant à un autre référentiel.
- [ ] Afficher le nom technique, le libellé et la description en complément de
  la catégorie choisie.
- [ ] Mutualiser le comportement JavaScript entre les formulaires d'ajout et
  d'édition, sans script inline.
- [ ] Afficher des messages français lorsque le référentiel est indisponible,
  vide ou incompatible avec une règle existante.

### Tests et documentation

- [ ] Tester l'API de consultation des ressources, champs et sections actifs.
- [ ] Tester la validation ORM des champs et sections inconnus, inactifs et
  valides.
- [ ] Tester l'ajout et l'édition Web avec changement de type et de ressource.
- [ ] Tester le refus d'une requête fabriquée contenant une cible d'un autre
  type ou d'une autre ressource.
- [ ] Tester la migration et la consultation des autorisations historiques
  contenant des valeurs comme `job_section` et `rh_section`.
- [ ] Documenter le fonctionnement du référentiel dans le README local du
  domaine sécurité.

## Critères d'acceptation

1. L'opérateur ne saisit plus librement une ressource, un champ ou une zone
   lors de la création d'une autorisation.
2. La ressource est choisie dans une liste exhaustive des ressources actives.
3. Un type de cible est choisi explicitement : champ ORM ou zone fonctionnelle.
4. La cible est choisie dans la liste exhaustive correspondante de la
   ressource sélectionnée.
5. Le libellé métier, le code technique et la description permettent
   d'identifier la cible sans ambiguïté.
6. Une cible inconnue, inactive ou incohérente est refusée par l'API même si
   la requête contourne l'IHM.
7. Les doublons d'autorisation continuent d'être refusés avec un message
   français exploitable.
8. L'ajout et l'édition utilisent le même comportement de sélection.
9. Les règles historiques liées à une définition désactivée restent
   consultables et éditables sans permettre de créer de nouvelles règles
   invalides.

## Vérifications

Exécuter les commandes adaptées via le Makefile racine :

- `make test.api` pour les endpoints et formulaires Web ;
- `make test.unit` pour les règles ORM et services de métadonnées ;
- `make test.integration` pour les migrations et référentiels SQL ;
- `make test.style` pour les tests ajoutés ;
- `make cs.check` et `make stan` si du PHP est modifié.

## Références

L’implémentation introduit la migration `20261106100000`, le référentiel
`authorization_targets`, le catalogue JSON `get-form-schema.json` et le
sélecteur partagé des formulaires d’ajout et d’édition. Les règles historiques
restent éditables, tandis que les nouvelles cibles doivent être actives.

- `src/Controller/FieldAuthorizationsController.php`
- `src/Controller/Api/FieldAuthorizationsController.php`
- `src/Model/Table/FieldAuthorizationsTable.php`
- `src/Model/Table/FieldDefinitionsTable.php`
- `src/Model/Table/AuthorizationTargetsTable.php`
- `src/Service/Metadata/FieldMetadataService.php`
- `templates/FieldAuthorizations/add.php`
- `templates/FieldAuthorizations/edit.php`
- `webroot/js/views/FieldAuthorizations/form.js`
- [ADR 0045 — Gestion CRUD des autorisations de champs](../adr/0045-gestion-crud-field-authorizations.md)
- [ADR 0046 — Standardisation du CRUD des autorisations de champs](../adr/0046-standardisation-crud-field-authorizations.md)
