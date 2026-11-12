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

## Autorisation des champs

Les mutations des formulaires Web filtrent leurs données avec les helpers
`fieldAuthorizationSchema()` et `authorizedRequestData()` hérités de
`AppController`, avant toute hydratation ORM. La Policy d’action reste
nécessaire ; la matrice `field_authorizations` ajoute le contrôle au niveau du
champ.

Dans les formulaires de DAE, le département précède l'assistante. Le script
`webroot/js/views/Applicationforms/applicationform-treeselect.js` recharge les
options après chaque changement de département et conserve uniquement une
assistante encore éligible. Lorsqu'une seule assistante est disponible, il
masque le sélecteur, affiche son nom et transmet son identifiant dans un champ
masqué si le champ est modifiable. En lecture seule, il utilise l'option déjà
fournie par le serveur sans rendre le champ modifiable. Le script recharge aussi
les responsables associés au département sélectionné, sans héritage depuis un
parent, et ignore les réponses tardives. Les chargements sont suspendus lorsque
le département est en lecture seule. La sauvegarde conserve le filtrage des
assistantes côté serveur.

## Administration des départements

`DepartmentsController` rend l'écran `/departments` réservé aux opérateurs
autorisés par `DepartmentPolicy`. Il présente l'arbre hiérarchique, le
formulaire d'édition avec les responsables et codes CGR sélectionnables en
multiple, ainsi que la sous-grille des codes CGR ; les mutations et le calcul
d'impact sont délégués à l'API `DepartmentsController`.

## Redirections post-authentification

`UsersController` ne suit que les URL de retour locales validées par le composant
Authentication. Les routes dont la validité dépend d'un état de session, telle
que `revert_identity`, sont remplacées par `/users/index` lorsque cet état est
absent après la connexion.

## ADRs Associés
* [ADR 0046 : Standardisation du CRUD hybride](../../docs/adr/0046-standardisation-crud-field-authorizations.md)
* [ADR 0060 : Un message Flash par champ invalide](../../docs/adr/0060-un-flash-par-champ-invalide-web.md)
* [ADR 0064 : Administration de l'arbre des départements](../../docs/adr/0064-administration-arbre-departements.md)
* [ADR 0065 : ACL automatique des mutations Web et API](../../docs/adr/0065-acl-automatique-des-mutations-web-api.md)

## Règles locales de circuit

La page `/validation-circuit-rules` est autorisée par
`ValidationCircuitRulePolicy`, également utilisée pour sa navigation. Elle
délègue les données et mutations au contrôleur API et au service métier.
La section des habilitations est rendue uniquement pour un super-administrateur.
Les actions métier proviennent de `ValidationCircuitRulesActions` et passent
par `ActionHelper`. Le [guide opérateur](../../docs/validation-circuit-rules.md)
décrit les accès et la configuration manuelle.
