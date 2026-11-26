# 0038 — Respecter l'affichage sans défilement

**Statut :** En cours

**Priorité :** Moyenne — ergonomie de saisie

**Dépendance :** [0037 — Compléter les données imprimées du PDF](0037-completer-donnees-imprimees-pdf.md).

## Objectif

Adapter la vue de saisie aux dimensions d'écran et au parcours convenus au
0030, en précisant ce que signifie l'exigence « sans ascenseur ».

La réponse à Q-0030-05 fixe un écran de référence de 1680 × 1050 et une
fenêtre non maximisée avec une zone de page de 1440 × 900. Un contrôle
complémentaire utilise une zone de page de 1366 × 768. Le zoom est de 100 %
aux deux tailles. Le plan approuvé retient Chrome, Edge et Firefox récents.

Pour ce ticket, « sans ascenseur » signifie que le document ne défile pas
globalement. Les quatre zones de saisie restent affichées ensemble; une zone
dont le contenu dépasse sa hauteur disponible défile indépendamment et peut
recevoir le focus clavier. Les actions de formulaire restent visibles. Le
panneau des commentaires conserve son défilement interne.

## Travail attendu

- [ ] Effectuer la recette aux deux tailles de page dans Chrome, Edge et
  Firefox récents et consigner les versions utilisées.
- [x] Ajuster la disposition des quatre zones de saisie et garder les actions
  du formulaire accessibles; les zones longues défilent indépendamment.
- [x] Conserver les règles d'autorisation et les erreurs de validation; rendre
  chaque région de saisie accessible au clavier.
- [x] Vérifier l'ADR 0048 : les cinq zones fonctionnelles et le panneau de
  commentaires restent conformes; aucun nouvel ADR n'est nécessaire.
- [x] Documenter une recette visuelle répétable et ses limites ci-dessous.

## Critères d'acceptation

1. Le parcours répond à la définition et aux dimensions confirmées au 0030.
2. Aucun champ, avertissement, action ou erreur de validation ne devient
   inaccessible.
3. Les vues réduites restent utilisables au clavier et ne se chevauchent pas.
4. Toute évolution architecturale de l'ADR 0048 est proposée dans un nouvel
   ADR et non appliquée rétroactivement.

## Vérifications

- `make test.unit`
- `make test.api` pour le rendu Web et les droits
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié
- Recette visuelle aux résolutions et navigateurs convenus

## Recette visuelle reproductible

Utiliser des données synthétiques et un compte de test autorisé. Sur les pages
de création et de modification :

1. Régler le zoom navigateur à 100 %.
2. Contrôler une zone de page de 1440 × 900 dans une fenêtre non maximisée sur
   un écran de référence de 1680 × 1050, puis une zone de page de 1366 × 768.
3. Vérifier que les quatre zones restent visibles, que leurs titres ne
   disparaissent pas pendant le défilement interne et que Créer, Enregistrer
   ou Annuler reste visible.
4. Parcourir les régions et les champs avec Tabulation. Dans une région longue,
   utiliser les flèches ou Page suivante/précédente et vérifier que les champs
   du début et de la fin restent atteignables.
5. Soumettre un formulaire incomplet et vérifier au clavier l'accès au champ
   invalide et à son message. Dans la page de modification, ouvrir aussi le
   panneau Commentaires et vérifier son parcours clavier.
6. Dans les outils de développement, vérifier l'absence de défilement global :

   ```js
   const page = document.scrollingElement;
   page.scrollHeight <= page.clientHeight && page.scrollWidth <= page.clientWidth;
   ```

7. Répéter les contrôles dans Chrome, Edge et Firefox récents; consigner le
   navigateur, sa version et la taille réelle de la zone de page.

Cette recette est manuelle et ne compare pas les captures à une image de
référence. Elle vérifie le document, les régions défilantes, les actions et le
parcours clavier avec les données utilisées; elle ne remplace pas les essais
sur les postes métier ni une vérification exhaustive des technologies
d'assistance.

## Vérifications exécutées le 26 novembre 2026

- `make test.unit` : 244 tests réussis.
- `make test.api` : 317 tests réussis.
- `make test.style`, `make cs.check` et `make stan` : réussis.
- Chrome 158.0.8127.88 : captures headless des pages de création et de
  modification aux zones de page 1440 × 900 et 1366 × 768. Les instantanés
  proviennent des fixtures synthétiques des tests HTTP; les quatre zones et
  les actions restent visibles, avec défilement limité aux zones longues.
- Firefox 153.7.0esr est installé, mais sa capture headless échoue dans cet
  environnement (`RenderCompositorSWGL failed mapping default framebuffer`).
  Edge n'est pas installé.
- La recette clavier et les essais dans les trois navigateurs restent à
  exécuter dans une session interactive; le pont de navigateur de cette
  session est indisponible.

## Références

- [ADR 0048 — Découpage de l'IHM Applicationform](../adr/0048-decoupage-ihm-zones-applicationform.md)
- [ADR 0013 — Architecture frontend JavaScript](../adr/0013-architecture-frontend-js.md)
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)
