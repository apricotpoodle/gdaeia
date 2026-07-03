# ADR 0007 : Gestion des accès Git (Multi-comptes SSH)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Le développement nécessite parfois de pousser du code sur un dépôt appartenant à une organisation spécifique ou à un compte personnel distinct (ex: `apricotpoodle`), tout en utilisant une machine configurée avec une clé SSH professionnelle par défaut. Cela génère des erreurs `Permission denied (publickey)`.

## Décision
Nous utilisons les **Alias SSH** pour gérer de multiples identités de manière transparente (KISS & DRY).
1. Le fichier `~/.ssh/config` de la machine hôte doit définir un alias (ex: `Host github-apricot`) pointant vers la clé SSH adéquate avec la directive `IdentitiesOnly yes`.
2. L'URL distante du dépôt local (`git remote`) doit utiliser cet alias à la place du traditionnel `github.com` (ex: `git@github.com-perso:apricotpoodle/gdaeia.git`).

## Justification
Cette méthode évite d'avoir à manipuler manuellement le `ssh-agent` à chaque session de terminal ou de risquer des commits avec la mauvaise identité cryptographique.