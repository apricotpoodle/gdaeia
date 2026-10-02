# Instructions pour les contributeurs et les agents

## Isolation par branche Git

Avant de modifier un fichier :

1. Inspecter le dépôt avec `rtk git status --short --branch`.
2. Ne jamais modifier directement `main`.
3. Lorsque l'arbre de travail est propre, créer une branche dédiée avant toute
   modification :
   - `docs/...` pour la documentation et les ADRs ;
   - `feature/...` pour une fonctionnalité ;
   - `fix/...` pour une correction ;
   - `refactor/...` pour un refactoring.
4. Lorsque des modifications préexistantes sont présentes, les identifier et
   les préserver. Ne jamais les réinitialiser, les mettre de côté, les écraser
   ou les mélanger à la nouvelle tâche sans décision explicite.
5. Vérifier à nouveau la branche active avant la première écriture.

Cette règle s'applique aux contributeurs humains et aux agents IA, y compris
pour les changements documentaires. Lors de la livraison, indiquer le nom de
la branche et les fichiers modifiés dans le cadre de la tâche.

## Convention d'exécution des commandes

Les commandes shell doivent utiliser le préfixe `rtk`, conformément aux
instructions du dépôt.

## Messages de commit

Chaque commit doit toujours être atomique : il ne porte que sur une seule
intention cohérente et ne mélange jamais des changements fonctionnels,
documentaires ou techniques sans rapport. Les sujets distincts doivent être
répartis dans des commits distincts.

Toute demande de création d'un commit, qu'elle provienne d'un humain ou d'un
agent IA, nécessite la validation explicite de l'opérateur humain avant
d'exécuter `git commit`. Préparer les changements indexés et proposer le
message complet ne constitue pas cette validation.

Avant chaque commit, vérifier que le message :

- respecte la norme Conventional Commits ;
- utilise un scope lorsqu'il apporte une précision utile ;
- contient une description rédigée en français ;
- couvre uniquement les changements atomiques indexés pour ce commit ;
- ne mélange pas plusieurs intentions ou sujets indépendants.

Utiliser le format suivant :

```text
<type>(<scope>): <description en français>
```

Les types usuels sont :

- `feat` : fonctionnalité ;
- `fix` : correction ;
- `docs` : documentation ;
- `refactor` : refactorisation ;
- `test` : tests ;
- `chore` : maintenance.

Exemple :

```text
docs(git): formaliser les règles d’isolation des branches
```

Le type et la structure du commit peuvent être contrôlés automatiquement, mais
la rédaction française reste une exigence de revue manuelle.
