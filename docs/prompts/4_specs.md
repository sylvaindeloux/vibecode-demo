Avant de coder, je veux une spécification produit complète dans `docs/spec.md`, en anglais.

## Contexte fixé
- Démo qui tourne en local uniquement : pas d'hébergement, pas d'authentification, un seul utilisateur implicite.
- Stack : Symfony (dernière version stable), SQLite (Doctrine), Twig, Symfony UX, AssetMapper, CSS natif du design system `cra-design-system`. Pas de Tailwind.
- Le périmètre fonctionnel et les écrans sont décrits dans la skill `cra-design-system` (references/screens.md, calendar.md, print.md) : pars de là.

## Principes directeurs (ils priment sur tout le reste)
- **Le moins de code possible.** Chaque ligne doit se justifier.
- **Ne pas réinventer la roue** : utiliser en priorité ce que fournissent Symfony, Symfony UX, Doctrine et Twig (MakerBundle, Form, Validator, Twig Components, Live Components, Turbo, UX Icons, Translation, Intl, attributs de mapping, value resolvers, etc.).
- **Aucune dépendance tierce** en dehors des écosystèmes Symfony, Doctrine et Twig, sauf si c'est impossible autrement. Dans ce cas, justifie-la dans la spec.
- **Appliquer les bonnes pratiques** des skills `symfony-bp-*`.

## Ce que tu dois faire
1. Lis la skill `cra-design-system` en entier, puis les skills `symfony-bp-*`.
2. Écris `docs/spec.md` avec les sections suivantes :
    - vision et périmètre, y compris ce qui est hors périmètre ;
    - user stories avec critères d'acceptation ;
    - modèle de données : entités, champs, types, contraintes, relations ;
    - règles métier : états d'un jour, calcul des totaux, jours fériés, bornes de dates, suppression d'un client qui a des jours saisis, contenu du CRA imprimé, etc. ;
    - écrans et routes ;
    - données de démo (fixtures) et commande de réinitialisation ;
    - choix techniques : pour chaque besoin, la brique Symfony / UX / Doctrine / Twig utilisée ;
    - exigences non fonctionnelles : accessibilité, navigateurs, qualité de code, tests.

## Règles
- Réponds-moi en français ; le contenu de la spec et du code est en anglais.
