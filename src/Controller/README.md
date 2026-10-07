# Module Contrôleurs Web (`src/Controller`)

Ce répertoire contient les contrôleurs d'interface utilisateur (UI).
Conformément à notre architecture (Fat Models, Skinny Controllers), ces classes ont pour **unique responsabilité** de livrer les vues HTML au navigateur et de gérer les redirections de base. La manipulation massive de données est déléguée au module `Api`.

## Architecture Hybride
Certaines actions, comme la suppression (`delete`), sont hybrides : elles détectent si la requête est XHR/AJAX pour renvoyer du JSON (négociation de contenu), ou effectuent une redirection standard avec un message Flash le cas échéant.

## Référentiels métier

Les sept nomenclatures utilisées par les demandes (`Contracttypes`, `Hiringreasons`,
`Professionalcategories`, `Worktimes`, `Periods`, `Budgetfeatures` et `Yesnos`)
partagent `ReferenceController` pour le rendu Web de leurs grilles et formulaires.
Leurs mutations sont exposées par les contrôleurs API correspondants et restent
réservées aux superadministrateurs via `ReferencePolicy`.

## Erreurs des formulaires Web

Après un échec de sauvegarde, les contrôleurs Web appellent `AppController::flashValidationErrors()` avec l'entité et sa ressource ORM. Cette méthode utilise `ValidationErrorPresenter` pour produire un message Flash par champ invalide, avec tous ses motifs distincts. Les formulaires liés à l'entité conservent les erreurs CakePHP près des contrôles.

Une sauvegarde échouée sans erreur d'entité reçoit un message générique. Le parcours de demande de réinitialisation du mot de passe garde son message uniforme, qu'une adresse soit connue ou non.

## Redirections post-authentification

`UsersController` ne suit que les URL de retour locales validées par le composant
Authentication. Les routes dont la validité dépend d'un état de session, telle
que `revert_identity`, sont remplacées par `/users/index` lorsque cet état est
absent après la connexion.

## ADRs Associés
* [ADR 0046 : Standardisation du CRUD hybride](../../docs/adr/0046-standardisation-crud-field-authorizations.md)
* [ADR 0060 : Un message Flash par champ invalide](../../docs/adr/0060-un-flash-par-champ-invalide-web.md)
