# Instructions pour les contributeurs et les agents

## Isolation par branche Git

Avant de modifier un fichier :

1. Inspecter le dépôt avec `rtk git status --short --branch`.
2. Ne jamais modifier directement `main`.
3. Proposer une branche dédiée inédite, attendre la confirmation explicite de
   l'opérateur humain, puis la créer avant toute modification lorsque l'arbre
   de travail est propre :
   - `docs/...` pour la documentation et les ADRs ;
   - `feature/...` pour une fonctionnalité ;
   - `fix/...` pour une correction ;
   - `refactor/...` pour un refactoring.
4. Lorsque des modifications préexistantes sont présentes, les identifier et
   les préserver. Ne jamais les réinitialiser, les mettre de côté, les écraser
   ou les mélanger à la nouvelle tâche sans décision explicite. Un worktree
   dédié permet de les laisser dans leur arbre de travail.
5. Si le checkout est sur `main` et contient des modifications, prévenir
   l'opérateur humain avant toute opération qui modifie le dépôt. Présenter
   les fichiers concernés et proposer des suites possibles, par exemple un
   commit distinct sur une branche dédiée. Attendre sa décision explicite
   avant de créer une branche ou un worktree, d'indexer, de modifier ou de
   commiter.
6. Vérifier à nouveau la branche active avant la première écriture.

Ne jamais réutiliser une branche préexistante pour une nouvelle tâche. Si le
nom proposé existe déjà, proposer un nom inédit et demander confirmation.

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
- contient une description rédigée en français, généralement à l'infinitif,
  de 50 caractères au plus et sans point final ;
- couvre uniquement les changements atomiques indexés pour ce commit ;
- ne mélange pas plusieurs intentions ou sujets indépendants.

Utiliser le format suivant :

```text
<type>(<scope>): <description française à l'infinitif>

<corps facultatif : problème, décision et effets de bord>

<pied facultatif : BREAKING CHANGE, Fixes #…, Refs #…>
```

Le corps est recommandé pour un changement complexe. Il explique le quoi et
le pourquoi, et ses lignes ne dépassent pas 72 caractères. Le pied permet de
référencer des tickets ou une rupture de compatibilité.

Les types autorisés sont :

- `feat` : fonctionnalité ;
- `fix` : correction ;
- `docs` : documentation ;
- `refactor` : refactorisation ;
- `perf` : performance ;
- `test` : tests ;
- `build` : build ou dépendances ;
- `ci` : intégration continue ;
- `style` : formatage sans changement de logique ;
- `chore` : maintenance.

Exemple :

```text
docs(git): formaliser les règles d’isolation des branches
```

Le type et la structure du commit peuvent être contrôlés automatiquement, mais
la rédaction française reste une exigence de revue manuelle.

Ne pas créer de branche, commit, push, pull request, fusion ou exécuter
`finish_feature` sans confirmation explicite de l'opérateur humain.
