# 0011 — Remettre l’application en conformité PHPStan

**Statut :** Clôturé

**Priorité :** Haute

## Prompt Codex

Reprends la remise en conformité PHPStan. Lis `AGENTS.md`, puis ce ticket et
l’ADR 0053. Vérifie `git status` à la racine et dans `app/`, puis exécute
`make stan`. Isole un petit lot cohérent de fichiers qui ne chevauche pas des
modifications locales existantes ; corrige les types natifs, déclarations de
propriétés et PHPDoc sans masquer les erreurs par une configuration globale,
ni changer le comportement métier, le schéma, les routes ou les contrats API.
Exécute les tests adaptés au lot, `make test.style`, `make cs.check` et
`make stan`. Ne crée ni branche, ni commit, ni pull request sans accord
explicite.

## Contexte

PHPStan est disponible dans le conteneur, mais `make stan` relève encore plus
de 1 000 erreurs réparties sur 64 fichiers. Cette dette est indépendante des
évolutions fonctionnelles en cours et ne doit pas empêcher leur validation
ciblée lorsqu'une dérogation est explicitement documentée.

## Objectif

Supprimer les erreurs PHPStan globales sans modifier les règles métier, le
schéma, les routes ni les contrats API.

## Travaux à réaliser

* Établir un état initial reproductible avec `make stan`.
* Corriger les fichiers par lots cohérents et isolés.
* Privilégier les déclarations de propriétés, les types natifs et les PHPDoc
  exacts plutôt que des suppressions ou des ignorations globales.
* Créer un ticket distinct pour toute correction qui modifie le comportement.

## Critères d’acceptation

* `make stan` réussit sans erreur.
* Les tests adaptés aux fichiers traités sont exécutés et réussissent.
* Aucun paramètre global PHPStan ne masque une erreur existante.

## Réalisation

Les annotations IDE Helper sont désormais générées avec des associations et
paramètres génériques compatibles PHPStan. La cible `ide-helper.sync` purge les
annotations obsolètes lors de la synchronisation.

Vérifications réussies le 17 septembre 2026 :

* `rtk make stan` : aucune erreur ni avertissement ;
* `rtk make cs.check` ;
* `rtk make test.unit` : 38 tests, 115 assertions ;
* `rtk make test.integration` : 77 tests ;
* `rtk make test.style`.

## Références

* [Configuration PHPStan](../../phpstan.neon)
* [ADR 0053 — Stratégie de tests unitaires et intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
