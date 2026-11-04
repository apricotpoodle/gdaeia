# Sous-workflow — Visibilité et gérabilité des utilisateurs

Le manifeste [`manifest.json`](manifest.json) est la source de vérité de ce
sous-workflow. Les sous-tickets sont exécutés dans l'ordre de leurs
dépendances ; aucun ticket ne doit être traité avant que ses prérequis soient
terminés.

Le ticket parent est
[`0025 — Séparer la visibilité et la gérabilité des utilisateurs`](../0025-separer-visibilite-et-manageabilite-utilisateurs.md).
La décision d'architecture associée est l'[ADR 0073](../../adr/0073-visibilite-et-manageabilite-des-utilisateurs.md).

## Règle d'or

La visibilité autorise la lecture. La gérabilité autorise une mutation
précise. La présence ou l'absence d'une action dans l'interface ne remplace
jamais la vérification côté serveur.

## Ordre d'exécution

```text
0001 → 0002 → 0003 → 0004
                    ├→ 0005
                    ├→ 0006
                    └→ 0007 → 0008 → 0009
```

## Vérifications communes

Les tickets applicatifs utilisent les commandes du dépôt racine, notamment
`make test.unit`, `make cs.check`, `make stan` et les suites adaptées au code
modifié. Les tickets qui touchent les Models, Policies, contrôleurs ou API
doivent ajouter les tests de non-régression correspondants.

