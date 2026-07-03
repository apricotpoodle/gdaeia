# ADR 0023 : Découplage des éléments UI via les patrons Builder et Factory

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
La génération dynamique de la colonne d'actions (cf. ADR 0022) nécessitait la manipulation de chaînes de caractères HTML complexes (classes CSS multiples, icônes, attributs de données). Coder ce balisage en dur crée un couplage fort, expose à des erreurs de syntaxe HTML et rend les évolutions graphiques fastidieuses.

## Décision
1. Création de la classe `ButtonBuilder` pour abstraire la mécanique de construction des balises HTML (gestion des attributs, concaténation des classes).
2. Création de la classe statique `ButtonFactory` agissant comme l'unique source de vérité (Single Source of Truth) pour la définition sémantique et visuelle des boutons d'action.
3. Le `TabulatorBuilder` sous-traite systématiquement le rendu de ses cellules d'action à cette Fabrique.

## Justification
Cette architecture obéit au principe de Responsabilité Unique (Single Responsibility) et au principe Ouvert/Fermé (Open/Closed Principle) des règles SOLID. Les designers ou intégrateurs peuvent modifier globalement l'interface (ex: migration Bootstrap 5 vers 6, changement de librairie d'icônes) dans la `ButtonFactory` sans jamais impacter la logique de traitement des données de Tabulator.
