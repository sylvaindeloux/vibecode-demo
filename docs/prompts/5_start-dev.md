Implémente EasyCRA de bout en bout, en toute autonomie, à partir de `docs/spec.md`.

## Sources de vérité
- `docs/spec.md` : périmètre, règles métier, décisions.
- `CLAUDE.md` : workflow, principes directeurs, langue.
- Skills : `cra-design-system` (UI, CSS, composants, calendrier, impression) et `symfony-bp-*` (bonnes pratiques).
  En cas de contradiction : spec > CLAUDE.md > skills. Consigne l'arbitrage dans la section « Decisions » de la PR.

## Principes (non négociables)
- Le moins de code possible. Utilise d'abord ce que fournissent Symfony, Symfony UX, Doctrine et Twig (MakerBundle pour générer, Form, Validator, Twig Components, Turbo, UX Icons, Translation, etc.).
- Aucune dépendance tierce hors des écosystèmes Symfony, Doctrine et Twig, sauf impossibilité justifiée dans la PR.
- Réutilise les templates, composants, contrôleurs Stimulus et CSS de `cra-design-system` tels quels ; n'en recrée pas.

## 1. Plan
Avant de coder, écris `docs/plan.md` : la liste ordonnée des features, découpée selon la spec, avec pour chacune :
- le but, en une ligne ;
- les fichiers concernés ;
- les critères de fin (tests qui doivent passer, écran qui doit fonctionner).
  Commence par le socle technique : projet Symfony, SQLite, AssetMapper, Symfony UX, intégration du design system, outils qualité (PHPStan au niveau max, PHP-CS-Fixer, PHPUnit), scripts `composer` (`lint`, `test`, `check`), CI GitHub Actions, fixtures de démo et commande de réinitialisation.
  Chaque feature suivante doit être livrable et testable seule. Commite le plan en premier.

## 2. Boucle d'implémentation (feature par feature, sans t'arrêter)
Pour chaque feature du plan :
1. Implémente-la, avec ses tests : tests fonctionnels `WebTestCase` pour les écrans, tests unitaires pour la logique métier.
2. Vérifie. Tout doit être vert avant de commiter :
    - `composer lint` (PHP-CS-Fixer, PHPStan, `lint:twig`, `lint:container`, `lint:yaml`) ;
    - `composer test` ;
    - `php .claude/skills/cra-design-system/scripts/check-styles.php` ;
    - `php bin/console doctrine:schema:validate` ;
    - `php bin/console debug:translation fr --only-missing` : aucune clé manquante.
3. Commite de façon atomique :
    - un commit = un changement cohérent et vert (plusieurs commits par feature si besoin) ;
    - messages au format Conventional Commits, en anglais (`feat(calendar): add day state cycling`, `test(client): …`, `chore: …`) ;
    - jamais de commit « WIP », jamais de code commenté ni de fichiers temporaires.
4. Coche la feature dans `docs/plan.md` (dans le même commit ou le suivant).
5. Pousse sur ta branche après chaque feature.

Si tu bloques, ne t'arrête pas et ne me pose pas de question : choisis la solution la plus simple conforme aux principes, note-la dans `docs/plan.md` (section « Decisions & issues ») et continue. Si une vérification est impossible dans l'environnement cloud (navigateur, impression), dis-le explicitement dans le rapport au lieu de la déclarer réussie.

## 3. Fin
Quand tout le plan est coché :
- relance l'ensemble des vérifications et vérifie que la CI est verte ;
- vérifie que `composer install` suivi de la commande de réinitialisation de la démo, puis `symfony serve`, donne une app utilisable ;
- mets à jour le `README.md` : prérequis, installation, lancement de la démo, commandes utiles ;
- ouvre une PR vers `master` avec : le résumé par feature, le tableau « Decisions », les problèmes rencontrés, ce qui n'a pas pu être vérifié, et comment tester la démo en 5 étapes.

## Communication
Pas de message pendant le travail. Un seul rapport final, en français : ce qui est fait, le lien de la PR, les décisions prises, les problèmes rencontrés, les points à vérifier par moi.
