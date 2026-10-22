# ADR 0065 — ACL automatique des mutations Web et API

- Date : 2026-10-15
- Statut : Accepté
- Dépendances : ADR 0042, ADR 0045, ADR 0046 et ADR 0052

## Contexte

L’application dispose d’une matrice `field_authorizations` capable de
décrire, pour un rôle, le niveau d’accès à un champ d’une ressource. Cette
matrice est toutefois appliquée uniquement par certains contrôleurs. Une
nouvelle entité ou un nouveau contrôleur peut donc oublier de filtrer les
données reçues avant leur hydratation ORM.

Cette situation crée une divergence entre l’interface, l’API et la sécurité
effective. Masquer un champ dans un formulaire ne suffit pas à empêcher une
requête forgée ou une tentative de mass-assignment.

## Décision

### Point d’application

Toute mutation de champs d’entité issue d’une requête Web ou API doit filtrer
les données avant `patchEntity()`. Le point d’entrée commun est
`FieldAuthorizationService`, utilisé par les helpers protégés de
`AppController`.

Les Policies continuent d’autoriser l’action globale (`add`, `edit`, `delete`,
etc.). L’ACL de champ est une vérification complémentaire et ne remplace pas
la Policy.

Les commandes CLI, imports, traitements de workflow et mutations internes ne
dépendent pas d’une identité HTTP et restent hors de cette ACL. Ils conservent
leurs listes blanches et leurs règles métier explicites.

### Niveaux et valeur par défaut

Pour une ressource configurée dans la matrice :

- `EDIT` conserve le champ dans les données soumises ;
- `VIEW` et `NONE` retirent le champ avant l’hydratation ORM ;
- un champ absent de la matrice est refusé par défaut.

Une ressource sans aucune règle reste temporairement compatible avec le
comportement historique pendant le déploiement progressif. Elle devient
protégée dès que sa matrice est initialisée.

Les super-administrateurs disposent d’un accès complet et ne sont pas filtrés
par la matrice.

### Catalogue et initialisation

`field_definitions` constitue le catalogue des champs administrables. Une
ressource ne doit être activée qu’après déclaration de ses champs mutables et
initialisation des droits de ses rôles.

La migration d’activation initialise les champs existants en `EDIT` pour
préserver le comportement fonctionnel actuel, puis applique les restrictions
sensibles explicitement. Le champ `Departments.base` est ainsi en `NONE` pour
les rôles non super-administrateurs.

Les champs système ou contrôlés par le serveur (`id`, dates, suppression
logique, identifiants d’auteur et clés de rattachement) restent également
protégés par les options CakePHP et les services métier.

## Justification

Cette décision applique la défense en profondeur de l’ADR 0042 à tous les
points d’entrée utilisateur, réduit la duplication des contrôles et rend les
écarts d’habilitation configurables dans une table administrable.

Le filtrage n’est pas placé dans `Table::save()` : une telle interception ne
pourrait pas distinguer correctement une requête HTTP, un import, une commande
ou un traitement de workflow. Le point d’entrée contrôleur conserve cette
distinction explicite.

## Conséquences

### Positives

- un champ `VIEW`, `NONE` ou non déclaré ne peut plus être modifié par une
  requête forgée sur une ressource activée ;
- le rendu des formulaires et le traitement serveur utilisent la même matrice ;
- l’ajout d’une ressource protégée suit une procédure vérifiable : catalogue,
  matrice, contrôleur et tests ;
- les super-administrateurs et les traitements techniques conservent leurs
  comportements légitimes.

### Négatives et mesures de maîtrise

- une matrice incomplète peut retirer des champs nécessaires aux opérateurs ;
- l’activation doit donc être accompagnée d’une migration complète et de tests
  de non-régression ;
- les actions métier de masse qui ne correspondent pas à une hydratation de
  champ restent protégées par leur Policy et leurs validations dédiées.

## Vérification

Le service ACL possède des tests unitaires couvrant le super-administrateur,
les niveaux `EDIT`/`VIEW`/`NONE`, le refus des champs absents et la transition
des ressources non configurées. Les contrôleurs Web/API et le formulaire des
départements vérifient le filtrage avant sauvegarde, notamment pour
`Departments.base`.
