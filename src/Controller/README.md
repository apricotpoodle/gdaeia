# Module Contrôleurs Web (`src/Controller`)

Ce répertoire contient les contrôleurs d'interface utilisateur (UI).
Conformément à notre architecture (Fat Models, Skinny Controllers), ces classes ont pour **unique responsabilité** de livrer les vues HTML au navigateur et de gérer les redirections de base. La manipulation massive de données est déléguée au module `Api`.

## Architecture Hybride
Certaines actions, comme la suppression (`delete`), sont hybrides : elles détectent si la requête est XHR/AJAX pour renvoyer du JSON (négociation de contenu), ou effectuent une redirection standard avec un message Flash le cas échéant.

## ADRs Associés
* [ADR 0046 : Standardisation du CRUD hybride](../../docs/adr/0046-standardisation-crud-field-authorizations.md)