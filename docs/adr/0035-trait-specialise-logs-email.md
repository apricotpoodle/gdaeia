# ADR 0035 : Simplification de l'écriture des Logs (EmailLoggerTrait)

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte

Suite à la mise en place du routage des logs de courriels (ADR 0034), l'appel répété à la méthode `$this->log('Msg', 'level', ['scope' => ['email']])` au sein des contrôleurs et des mailers s'est avéré trop verbeux. Cela dégrade la lisibilité du code métier et viole le principe DRY (répétition du tableau de configuration du scope).

## Décision
1. Création d'un **Trait PHP** `EmailLoggerTrait` dans `src/Log/`.
2. Ce Trait expose une méthode sémantique et percutante : `traceEmail(string $message, string $level = 'info')`.
3. Ce Trait agit comme une façade (Facade Pattern) masquant la complexité du tableau de paramétrage natif de CakePHP.

## Justification
L'utilisation d'un Trait est la solution architecturale la plus propre en PHP pour partager des comportements transversaux entre des classes qui n'ont pas la même ascendance (comme `AppController` et `AppMailer`). Le nom `traceEmail` est concis et explicite ses intentions.
