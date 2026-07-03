# Style Core : Tabulator Custom Theme

Ce répertoire est dédié exclusivement aux surcharges graphiques et à la personnalisation visuelle de la librairie Tabulator.js pour l'ensemble de l'application.

## Fichiers
* **custom-theme.css** : Centralise les styles transversaux partagés par toutes les grilles (messages de chargement, placeholders de vacuité, surcharges des en-têtes, etc.).

## Règle d'or (DRY)
Il est strictement interdit d'écrire des styles CSS spécifiques à Tabulator dans la feuille de style globale `app.css`. Toute modification visuelle de l'outil doit être rédigée ici afin de maintenir l'isolation graphique du composant.