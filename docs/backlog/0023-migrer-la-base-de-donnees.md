# 0023 — Migrer la base de données vers une nouvelle instance

**Statut :** À planifier

**Priorité :** Moyenne

## Contexte

La base MySQL est un service externe configuré par `DATABASE_URL`. Une
migration vers une autre base ou une nouvelle instance doit rester simple,
reproductible et réversible, sans exposer les identifiants dans le dépôt ni
modifier les paramètres de test.

## Objectif

Définir et documenter une procédure de migration de la base applicative vers
une nouvelle base MySQL, fondée sur un export, une restauration, l'application
des migrations et une vérification de cohérence.

## Travail attendu

- [ ] Définir les prérequis de la base cible : version MySQL compatible,
  moteur, encodage, collation, droits et connectivité réseau.
- [ ] Documenter la procédure d'export de la base source et de restauration
  vers la cible, sans inclure les secrets dans les commandes versionnées.
- [ ] Prévoir une sauvegarde vérifiable de la source et un plan de retour
  vers la source en cas d'échec.
- [ ] Configurer la cible uniquement via `.env` ou les variables
  d'environnement, en conservant `DATABASE_TEST_URL` séparée et protégée.
- [ ] Appliquer les migrations CakePHP sur la cible et vérifier les vues SQL
  gérées manuellement conformément aux ADR 0005 et 0006.
- [ ] Vérifier les comptes, les permissions MySQL, les index FULLTEXT, les
  vues de workflow, les contraintes et le volume de données.
- [ ] Ajouter une commande ou une procédure documentée simple, avec étapes
  d'arrêt/reprise de l'application et contrôle de l'état final.
- [ ] Réaliser une répétition complète sur une base de validation avant la
  migration de la base réellement utilisée.

## Critères d'acceptation

1. Une base cible vide peut être préparée puis alimentée avec une procédure
   documentée et reproductible.
2. Le dump restauré contient les données, contraintes, index et vues attendus.
3. Les migrations sont à jour et aucune migration ne reconstruit
   automatiquement une vue MySQL gérée manuellement.
4. L'application démarre sur la cible et les parcours de lecture, écriture,
   recherche FULLTEXT et workflow fonctionnent.
5. Les tests utilisent toujours exclusivement `DATABASE_TEST_URL` pour la
   base `daetf2_test`.
6. Les secrets, dumps et fichiers de configuration locaux ne sont pas
   versionnés.
7. Un contrôle de cohérence et un plan de retour sont disponibles avant la
   bascule.

## Vérifications

- `make test.unit`
- `make test.integration`
- `make test.workflow`
- `make test.api`
- vérification des migrations et vues SQL
- répétition sur une base MySQL de validation distincte
- contrôle de démarrage et de santé après bascule

## Références

- [ADR 0002 — Connexion MySQL et configuration](../adr/0002-connexion-base-de-donnees.md)
- [ADR 0005 — Modèle de données MySQL](../adr/0005-modele-de-donnees.md)
- [ADR 0006 — Gestion des vues dans les migrations](../adr/0006-gestion-des-vues-dans-les-migrations.md)
- [ADR 0053 — MySQL dédié aux tests](../adr/0053-strategie-de-tests-unitaires-et-integration.md)
- `README.md`
