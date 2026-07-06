# 🔄 Workflow Git : Le Feature Branch Flow

Ce document décrit le flux de travail standard de l'application (basé sur le GitHub/GitLab Flow). Toute nouvelle fonctionnalité, correction de bug ou expérimentation doit suivre ce cycle de vie strict afin de garantir la stabilité de la branche `main`.

---

## 🛠️ Étape 1 : Préparer et Créer la Branche

Ne développez **jamais** directement sur `main`. Mettez à jour votre poste et isolez votre travail.

**Règle de nommage :** `feature/nom-de-la-feature`, `fix/nom-du-bug`, `refactor/nom-du-module`.

### Via CLI (Standard)
```bash
git checkout main
git pull origin main
git fetch -p
git checkout -b feature/ma-nouvelle-fonctionnalite
```

### Via Lazygit
1. Allez sur le panneau Branches (touche 3).

2. Sélectionnez main, appuyez sur Espace pour vous y rendre, puis p pour tirer les nouveautés.

3. Appuyez sur n (new) pour créer une nouvelle branche et saisissez feature/ma-nouvelle-fonctionnalite.

## 💻 Étape 2 : Développer et Commiter (Le Quotidien)
Développez votre code et faites des commits atomiques (petits et logiques) avec des messages respectant la norme Conventional Commits (ex: feat(module): description).

### Via CLI
```Bash
git add src/MonFichier.php
git commit -m "feat(module): ajout de la nouvelle fonction"
```
### Via Lazygit
1. Panneau Files (touche 1).

2. Appuyez sur Espace sur les fichiers modifiés pour les indexer (Stage).

3. Appuyez sur c (commit) et rédigez votre message.

## 🚀 Étape 3 : Clôturer et Ouvrir la Pull Request (PR)
Une fois le développement terminé, il faut envoyer le code sur le serveur distant et demander sa fusion via une Pull Request (PR) pour déclencher la relecture et l'Intégration Continue (CI).

### L'approche Magique (Script Automatisé finish_feature)
Nous utilisons un outil maison qui gère le Push et la création de la PR en une seule commande.

#### Scénario A : En équipe (Créer la PR pour relecture)

```Bash
finish_feature
```
(Le script pousse la branche, ouvre votre éditeur pour rédiger le corps de la PR, la soumet sur GitHub/GitLab et vous rend la main).

#### Scénario B : En solo (Mode Expéditif "Auto-Merge")

```Bash
finish_feature -t "feat(module): ajout de la nouvelle fonction" --auto-merge
```

(Le script pousse, crée la PR avec ce titre, demande à GitHub de la fusionner automatiquement, rapatrie le main mis à jour et nettoie votre PC).

### L'approche Classique (CLI)
```Bash
git push -u origin feature/ma-nouvelle-fonctionnalite
gh pr create --fill
```

### L'approche Lazygit
1. Panneau Branches, appuyez sur P (Maj+p) pour pousser la branche locale.

2. Appuyez sur o (open) pour ouvrir la page GitHub/GitLab dans votre navigateur web et y créer la PR manuellement.

## 🧹 Étape 4 : Le Nettoyage Local (Fetch Prune)
Si vous avez utilisé `finish_feature --auto-merge`, cette étape est déjà faite pour vous.

Une fois la PR validée et fusionnée sur l'interface web, votre branche locale ne sert plus à rien et la branche distante n'existe plus. Il faut nettoyer votre environnement.

### Via CLI
```Bash
git checkout main
git pull origin main
git fetch -p             # Met à jour les références et supprime les branches distantes obsolètes
git branch -d feature/ma-nouvelle-fonctionnalite # Supprime la branche locale
```

### Via Lazygit
1. Espace sur main (pour changer de branche).

2. Appuyez sur p pour tirer le code fusionné.

3. Appuyez sur f pour rafraîchir et purger (Fetch Prune).

4. Sélectionnez votre ancienne branche de travail, et appuyez sur d (delete) pour la supprimer de votre PC.
