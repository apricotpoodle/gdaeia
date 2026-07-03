# ADR 0003 : Configuration de l'IDE (VSCodium) avec Docker

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
Le code source est édité localement via VSCodium, mais PHP et ses outils (PHPCS, PHPStan) ne sont installés qu'à l'intérieur du conteneur Docker (KISS). Cela génère des conflits, de faux positifs et des lenteurs si l'IDE tente de valider le code localement (notamment en scannant le dossier `vendor/`).

## Décision
1. **LSP (Language Server Protocol) :** Nous utilisons `PHP Intelephense` pour l'autocomplétion, car il gère intelligemment le dossier `vendor/` sans émettre de faux positifs de validation. Les extensions trop agressives comme `php-resolver` sont proscrites.
2. **Exclusion spatiale :** Le fichier `.vscode/settings.json` est configuré pour exclure formellement `vendor/`, `tmp/` et `logs/` de l'indexation, de la recherche et de l'écoute des modifications (`files.watcherExclude`).
3. **Qualité du code :** La validation de la syntaxe et des normes (PHP CodeSniffer, PHPStan) se fait *exclusivement* via les commandes du `Makefile` (`make cs.check`, `make stan`), c'est-à-dire à l'intérieur du conteneur.

## Justification
Cette approche garantit une *Developer Experience* (DX) fluide. L'IDE reste rapide et silencieux sur la machine hôte, tout en s'assurant que la validation du code reste stricte et reproductible pour n'importe quel développeur grâce à l'isolation Docker.