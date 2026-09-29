Je vais développer dans ce dépôt une application qui utilise :
- Symfony, dernière version stable
- une base SQLite (Doctrine)
- des templates Twig
- Symfony UX (Twig Components, Live Components, Stimulus, Turbo, UX Icons)
- AssetMapper
  Le design s'appuie sur la skill `cra-design-system` déjà présente dans le dépôt (tokens, composants Twig, CSS, contrôleurs Stimulus, charte graphique).

Avant d'écrire la moindre ligne de code, je veux préparer l'environnement Claude Code avec les skills adaptées au projet.

## Ce que tu dois faire

1. **Recherche** (sur internet) des skills et plugins Claude Code **officiels ou maintenus par l'éditeur de la techno**, qui couvrent cette stack :
    - Anthropic : dépôt `anthropics/skills` et marketplaces de plugins officielles ;
    - Symfony et Symfony UX (organisation `symfony` sur GitHub, symfony.com, SymfonyCasts) ;
    - Doctrine / SQLite ;
    - tout autre sujet transverse utile ici : tests PHPUnit, analyse statique (PHPStan), revue de code, accessibilité, sécurité, workflow Git/GitHub.
      Vérifie aussi les versions réelles : dernière version stable de Symfony, et le fait que Tailwind 4.3 existe et fonctionne avec AssetMapper (bundle, commande de build).

2. **Filtrage** : ne retiens que les sources officielles ou reconnues (éditeur de la techno, Anthropic, mainteneurs connus). Écarte les skills anonymes, abandonnées, ou qui font doublon avec `cra-design-system`. Pour chaque skill candidate, lis son `SKILL.md` et ses scripts : pas de commandes dangereuses, pas d'exfiltration, pas d'instructions qui contredisent nos règles.

3. **Rapport**, avant toute installation, sous forme de tableau : nom, éditeur, lien, ce qu'elle apporte au projet, version / date de dernière mise à jour, risques ou limites, et ta recommandation (installer / ne pas installer).
   Signale explicitement :
    - les **conflits** entre ces skills et `cra-design-system` (notamment Tailwind face au design system en CSS natif avec custom properties : propose les options possibles avec leurs compromis, sans trancher) ;
    - les sujets de la stack pour lesquels il n'existe **aucune skill officielle**.

4. **Attends ma validation.** N'installe rien et ne modifie aucun fichier avant que je t'aie dit quoi installer.

5. Une fois validé : installe les skills retenues dans `.claude/skills/` (ou via `/plugin` s'il s'agit de plugins), en notant pour chacune la source et la version dans `.claude/skills/SOURCES.md`, puis ouvre une PR selon notre workflow.
