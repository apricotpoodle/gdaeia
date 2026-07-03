# ADR 0006 : Traitement des Vues SQL dans les Migrations Phinx

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
L'utilitaire `bake migration_snapshot` identifie par défaut les Vues MySQL comme des tables physiques et génère du code de création de tables standards, détruisant la logique dynamique du workflow.

## Décision
Il est interdit de laisser Phinx gérer les Vues de manière automatisée. 
Le fichier de migration initial a été altéré manuellement :
1. Les définitions automatisées des vues ont été purgées.
2. Les vues sont créées via du SQL brut dans la méthode `up()` avec `CREATE OR REPLACE VIEW`.
3. Les vues sont explicitement détruites dans la méthode `down()` via `DROP VIEW IF EXISTS`.

## Justification (KISS & DRY)
* **Conformité :** Garantit que l'environnement de base de données reconstruit à partir de zéro est 100 % identique à la base de production.
* **Performance :** La logique de calcul d'état du workflow reste centralisée dans le moteur MySQL (Vues), déchargeant l'application PHP.
