# GDAETF2 — Gestion des demandes de recrutement

## Finalité de l’application

GDAETF2 est une application métier de gestion dématérialisée des demandes de
recrutement, appelées DAE dans le code applicatif. Elle centralise la saisie
d’une demande, son instruction par les différents acteurs concernés et la
traçabilité de la décision finale.

L’application s’adresse notamment aux demandeurs, responsables de département,
validateurs et administrateurs fonctionnels. Les données visibles et les
actions disponibles dépendent du rôle de l’utilisateur et de son périmètre
organisationnel.

## Parcours métier principal

Une demande de recrutement suit généralement ce cycle :

1. Un utilisateur crée une demande et renseigne le poste, le candidat, le
   motif de recrutement, le type de contrat, le temps de travail, la période,
   le département et les autres informations de référence nécessaires.
2. La demande peut être consultée, complétée ou modifiée selon les droits de
   l’utilisateur.
3. Le demandeur ou un utilisateur autorisé lance le cycle de validation.
4. L’application construit les étapes à partir de la séquence configurée pour
   le département et les rôles concernés.
5. Chaque validateur reçoit une notification, consulte la demande et vote pour
   l’accepter ou la refuser. Un commentaire peut être obligatoire selon la
   décision et la configuration du workflow.
6. Le cycle progresse étape par étape jusqu’à la décision finale. Les votes,
   commentaires, dates et acteurs sont conservés pour assurer la traçabilité.
7. Les demandes et les cycles qui dépassent le délai ouvré configuré peuvent
   être identifiés comme bloqués et faire l’objet d’un traitement spécifique.

Une demande peut également être exportée sous forme de PDF. Les résultats et
les prochaines étapes de validation sont transmis par courriel aux acteurs
concernés.

## Domaines fonctionnels

### Demandes de recrutement

Le module principal permet de créer, rechercher, consulter, modifier et
supprimer les demandes, ainsi que de consulter leur état et leur historique de
validation. L’interface Web est complétée par une API JSON utilisée par les
grilles et les actions asynchrones.

### Workflow de validation

Le workflow gère les séquences de validation, les étapes actives, les votes,
les statuts et les commentaires. Il permet notamment
de lancer, consulter, réinitialiser et suivre un cycle de validation, ainsi que
de repérer les cycles bloqués.

### Organisation et habilitations

L’application gère :

- les utilisateurs et leurs comptes ;
- les rôles et leurs droits ;
- l’arborescence des départements ;
- le rattachement des utilisateurs aux départements ;
- les menus accessibles par rôle ;
- les autorisations au niveau des champs de formulaire.

Les restrictions de périmètre sont appliquées côté serveur. Elles déterminent
à la fois les données consultables et les opérations autorisées. Un mécanisme
d’usurpation d’identité est prévu pour les utilisateurs habilités au support
et à l’administration technique.

### Référentiels métier

Les formulaires s’appuient sur des référentiels administrables, notamment les
types de contrat, motifs de recrutement, catégories professionnelles,
caractéristiques budgétaires, temps de travail, périodes et valeurs oui/non.

## Modèle métier simplifié

Le cœur de données est constitué des éléments suivants :

- `applicationforms` : demandes de recrutement ;
- `validation_workflow_runs` et `applicationvalidationsteps` : cycles et
  étapes de validation ;
- `validations` et `validation_visas` : votes et historique des validations ;
- `comments` : commentaires associés aux demandes ;
- `validationsequences` et `validationstatuses` : configuration et états du
  workflow ;
- `users`, `roles`, `departments` et tables de liaison : organisation et
  habilitations.

Des vues MySQL calculent également des informations utiles à la consultation,
comme l’état courant d’une demande, le rôle du prochain validateur et la
chronologie des visas.

## Organisation technique fonctionnelle

L’application est construite avec CakePHP 5.3 selon une architecture MVC :

- les contrôleurs Web rendent les pages HTML ;
- les contrôleurs `Api` exposent les données et mutations JSON ;
- les modèles et services portent les règles métier, les validations, les
  associations et les restrictions de données ;
- les Policies appliquent l’autorisation côté serveur ;
- les grilles Tabulator utilisent la pagination, le tri et le filtrage distants
  pour consulter les demandes, utilisateurs et référentiels.

Les scripts JavaScript de page se trouvent dans `webroot/js/views/` et les
composants front-end partagés dans `webroot/js/core/`. Tabulator, Luxon et
TreeSelectJS sont servis depuis `webroot/assets/`. Le layout actuel charge
encore Bootstrap 5.3.3 depuis un CDN ; cette dépendance fait l’objet d’une
standardisation documentée dans les ADR du projet.

## Documentation métier et architecture

Les décisions structurantes sont documentées dans [`docs/adr/`](docs/adr/), en
particulier :

- [ADR 0005 — Architecture de la base de données](docs/adr/0005-modele-de-donnees.md)
  pour le modèle métier et le workflow ;
- [ADR 0028 — Authentification et habilitations](docs/adr/0028-authentification-evolutive-et-impersonate.md)
  pour la sécurité des comptes et des sessions ;
- [ADR 0041 — Ségrégation des données](docs/adr/0041-segregation-donnees-model-custom-finders.md)
  pour les périmètres de visibilité ;
- [ADR 0044 — Recherche FULLTEXT](docs/adr/0044-filtre-recherche-fulltext-search-plugin.md)
  pour la recherche textuelle MySQL.

Les instructions de contribution et de développement sont regroupées dans
[`AGENTS.md`](AGENTS.md).
