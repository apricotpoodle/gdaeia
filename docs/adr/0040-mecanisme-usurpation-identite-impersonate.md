# ADR 0040 : Mécanisme d'usurpation d'identité (Impersonate)

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
Pour faciliter le support utilisateur, les administrateurs doivent pouvoir reproduire les bugs en naviguant dans l'application avec les droits et le point de vue d'un utilisateur final, sans lui demander son mot de passe.

## Décision
1. **Sécurité (Policy)** : L'action est protégée par `UserPolicy::canImpersonate`, qui autorise uniquement un profil `issuperuser` à usurper un profil classique (interdiction de faire de l'usurpation croisée entre administrateurs).
2. **Gestion de Session** : Plutôt que de stocker un flag boolean, l'identifiant du compte Super Admin d'origine est stocké dans la clé de session `Auth.original_user_id` lors de la bascule.
3. **Bascule Native** : Nous utilisons la méthode `setIdentity($targetUser)` du composant `Authentication` de CakePHP 5 pour remplacer l'objet en mémoire, ce qui s'applique instantanément au middleware global sans rompre l'architecture.
4. **Retour à la normale** : L'API JSON (`MenusController.js`) lit la présence de `Auth.original_user_id` pour faire apparaître le bouton de déconnexion spécifique (`revertIdentity`), qui restaure la session du Super Admin et purge la clé.

## Justification
Cette approche est non destructive. Elle évite d'altérer la base de données (pas de table de log de session complexe) et repose entièrement sur le stockage serveur volatil (Session). Elle est totalement transparente pour les contrôleurs : du point de vue de CakePHP, l'administrateur devient véritablement le client final.
