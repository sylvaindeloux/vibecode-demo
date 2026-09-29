Il n'existe pas de skill officielle pour les bonnes pratiques Symfony. Je veux que tu en crées à partir de la doc officielle :
https://symfony.com/doc/current/best_practices.html

## Ce que tu dois faire

1. **Lecture** : récupère la page, en entier. Relève toutes les sections de premier niveau. Quand une règle renvoie vers une autre page de la doc (tu en as besoin pour donner un exemple correct ou connaître la syntaxe actuelle), lis aussi cette page.

2. **Plan**, avant d'écrire quoi que ce soit. Donne-moi un tableau avec, pour chaque section :
    - le nom de la skill (`symfony-bp-<section>` en kebab-case, par exemple `symfony-bp-controllers`) ;
    - sa description (quand Claude doit la déclencher) ;
    - la liste des règles qu'elle contiendra ;
    - les points qui entrent en conflit ou qui recoupent nos skills existantes (`cra-design-system`, et les skills déjà installées dans `.claude/skills/`) ou notre stack (AssetMapper, SQLite, Symfony UX, Tailwind), avec les options possibles pour chaque conflit.
      Attends ma validation.

3. **Création**, une fois le plan validé : une skill par section dans `.claude/skills/symfony-bp-<section>/SKILL.md`.
    - Frontmatter YAML :
        - `name` ;
        - `description` en anglais, précise sur QUAND l'utiliser (par exemple : « Use when creating or modifying a Symfony controller, route or action… »).
    - Corps en anglais, court et actionnable, écrit pour un agent de développement :
        - les règles de la section, à l'impératif, une par ligne, chacune suivie d'une justification d'une ligne ;
        - pour chaque règle qui s'y prête, un exemple de code minimal « à faire » et, si c'est utile, « à éviter », à jour pour la dernière version stable de Symfony (attributs PHP, syntaxe actuelle) ;
        - une section « Source » avec l'URL de la page et la date de lecture.
    - Reste strictement fidèle à la doc : n'invente aucune règle. Si tu ajoutes une précision propre à notre projet, mets-la dans une section séparée « Project notes » et ne le fais que si je l'ai validée au point 2.
    - Si une section est trop courte pour justifier une skill, ou si deux sections vont logiquement ensemble, propose de les fusionner au point 2 plutôt que de décider seul.

4. **Index** : ajoute une skill `symfony-best-practices` qui résume en une ligne chaque skill `symfony-bp-*` et dit quand la lire. Elle sert de point d'entrée.

5. **Vérification** : chaque `SKILL.md` a un frontmatter valide, les descriptions ne se chevauchent pas, les exemples de code sont syntaxiquement corrects (`php -l` sur les extraits PHP quand c'est possible), et aucune règle ne contredit une autre skill du dépôt.

6. **PR** : ouvre une PR selon notre workflow. La description doit contenir le tableau final des skills créées et la liste des conflits que tu as résolus, avec la décision que j'ai prise.

## Règles

- Ne prends aucune décision technique ni métier seul : pose-moi la question, avec les options et ta recommandation.
- Réponds-moi en français. Tout ce qui va dans les skills et dans le code (contenu, identifiants, commentaires, commits, noms de branches) reste en anglais.
