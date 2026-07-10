# Module de Vue : Utilisateurs (Users)

Ce répertoire contient les gabarits (templates) CakePHP dédiés à l'interface d'administration des utilisateurs.

## Architecture (SoC - Separation of Concerns)
Conformément à l'ADR 0009, ces vues sont des "coquilles vides" :
- `index.php` : Se contente de générer le conteneur DOM (via le `TabulatorHelper`) sans interroger la base de données.
- La logique de rendu, le tri et l'affichage des 20 lignes sont pilotés par le script JavaScript associé situé dans `webroot/js/views/Users/index.js`.
- La sécurisation visuelle des colonnes est automatiquement prise en charge par l'infrastructure (ADR 0026).
