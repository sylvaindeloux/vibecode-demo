Crée un design system pour une petite application web qui aide les freelances français à gérer leur CRA (compte rendu d'activité) mensuel. Ce design system sera livré sous forme de skill Claude, destiné à être utilisé par Claude Code pendant le développement de l'application.

## Contexte technique
- Application PHP Symfony, rendu côté serveur avec des templates Twig.
- Composants d'interface réalisés avec Symfony UX :
    - Twig Components (composants anonymes dans `templates/components/`, utilisés via la syntaxe `<twig:Button>`) ;
    - Stimulus pour les interactions côté client ;
    - Turbo pour la navigation et les mises à jour partielles ;
    - Live Components possibles pour les composants interactifs (ex. calendrier) ;
    - UX Icons pour les icônes (ex. `<twig:ux:icon name="lucide:calendar" />`).
- CSS natif avec variables CSS (custom properties), servi par AssetMapper, sans étape de build. Pas de framework CSS.
- Pas de génération de PDF côté serveur : le CRA est une page HTML dédiée, optimisée pour l'impression, que l'utilisateur imprime au format PDF depuis son navigateur (fonction « Imprimer » > « Enregistrer au format PDF »).

## Contexte produit
- Utilisateurs : développeurs et consultants freelances qui facturent leurs clients à la journée.
- Parcours principal : chaque mois, l'utilisateur ouvre le calendrier et coche les jours travaillés (journée complète ou demi-journée). Il peut ajouter une courte note par jour, puis ouvre la version imprimable de son CRA et l'enregistre en PDF pour la joindre à sa facture.
- Périmètre : simple et ciblé. Pas de facturation, pas de comptabilité, pas de fonctionnalités d'équipe.
- Langue de l'interface : français.

## Pages de l'application
1. **Calendrier** (page d'accueil) : calendrier du mois en cours, sélecteur de client/mission, navigation entre les mois, barre récapitulative, bouton d'accès à la version imprimable du CRA.
2. **CRA imprimable** (accessible depuis le calendrier) : document du mois pour un client donné.
3. **Clients** (CRUD) :
    - liste des clients sous forme de tableau (nom, contact, nom de la mission, actions), avec état vide quand aucun client n'existe ;
    - formulaire de création et d'édition d'un client (nom, adresse, contact, nom de la mission) ;
    - suppression avec confirmation dans une modale.
4. **Profil** : infos du freelance affichées sur le CRA (nom, société, SIRET, adresse).

Toutes les pages partagent un layout commun : en-tête avec le nom de l'application, navigation principale (Calendrier, Clients, Profil) et bascule du thème clair/sombre. Sur mobile, la navigation reste accessible et utilisable au pouce.

## Ton et direction visuelle
- Propre, calme, professionnel mais sympa. L'idée : un outil qu'on ouvre une fois par mois et avec lequel on a fini en 2 minutes.
- Thèmes clair et sombre.
- Palette minimale : une couleur d'accent principale, des gris neutres et des couleurs sémantiques (succès, avertissement, erreur, info).
- Très bonne lisibilité, espacements généreux, coins légèrement arrondis, ombres douces au maximum.

## Design tokens
Toutes les valeurs de style (couleurs, typographie, espacements, rayons, ombres, breakpoints) sont définies sous forme de design tokens, organisés en deux niveaux :
- **Tokens primitifs** : les valeurs brutes de la palette et des échelles, nommées par leur valeur (ex. `blue-600`, `space-4`, `font-size-lg`). Ils ne sont jamais utilisés directement dans les composants.
- **Tokens sémantiques** : nommés par leur usage et référençant un token primitif (ex. `color-primary`, `color-surface`, `color-text-muted`, `color-day-worked`, `color-day-holiday`). Ce sont les seuls tokens utilisés dans les composants.

Le thème sombre redéfinit uniquement les valeurs des tokens sémantiques (via `prefers-color-scheme` et un attribut `data-theme` sur `<html>` pour le choix manuel). L'impression utilise toujours le thème clair, quel que soit le thème actif.

## Fondations à définir
- Tokens de couleur : palette primitive, puis tokens sémantiques (primaire, neutres, succès, avertissement, erreur, info, surfaces/fonds, bordures, texte, états du calendrier) pour les thèmes clair et sombre.
- Tokens typographiques : échelle (display, titres, texte courant, petit texte), graisses, interlignages, police mono pour les chiffres et totaux.
- Tokens d'espacement, de rayons, d'élévation (ombres) et de breakpoints (desktop-first, mais utilisable sur mobile).
- Iconographie : un seul jeu d'icônes Iconify (Lucide de préférence), utilisé via UX Icons.
- Accessibilité : contrastes WCAG AA minimum, états de focus visibles, navigation clavier du calendrier, attributs ARIA adaptés.

## Composants
Chaque composant est un Twig Component anonyme avec ses props déclarées via `{% props %}`, et fusionne les attributs passés via `attributes`. Les variantes et états sont exprimés par des classes CSS à modificateurs (convention BEM) ou des attributs `data-*`. Les interactions côté client passent par des contrôleurs Stimulus.

- Layout : en-tête, navigation principale (avec état de la page active), bascule de thème, conteneur de page.
- En-tête de page : titre, sous-titre optionnel, zone d'actions (ex. « Ajouter un client »).
- Boutons (primaire, secondaire, ghost, destructif ; tailles ; états chargement et désactivé ; variante icône seule avec libellé accessible).
- Champs de formulaire : texte, textarea, select, sélecteur de date, toggle, case à cocher. Ils doivent s'intégrer avec les form themes Symfony (fournir un form theme Twig qui applique le design system aux formulaires Symfony, y compris les erreurs de validation et les labels).
- Tableau de données : en-têtes, lignes, colonne d'actions (éditer, supprimer), survol, affichage adapté sur mobile.
- **Calendrier mensuel** (composant clé) :
    - États d'une case jour : vide, travaillé (journée complète), demi-journée, week-end, jour férié (fériés français), congé/absence, aujourd'hui, désactivé (hors du mois), survol/focus.
    - Actions rapides : « cocher tous les jours ouvrés », « vider le mois ».
    - Navigation entre les mois (précédent/suivant, sélecteur de mois).
    - Barre récapitulative : total des jours travaillés dans le mois.
    - Structure HTML sémantique (grille avec rôles ARIA adaptés), états portés par des attributs `data-*` pour pouvoir être pilotés par Stimulus ou un Live Component.
- Sélecteur de client/mission.
- Carte, badge, tag, infobulle, notification toast (compatible avec les messages flash Symfony), état vide (avec illustration ou icône, message et action principale).
- Modale basée sur l'élément natif `<dialog>` et un contrôleur Stimulus, avec une variante de confirmation d'action destructive (ex. suppression d'un client).
- Bouton « Imprimer / Enregistrer en PDF » qui déclenche `window.print()` via un contrôleur Stimulus.

## Écrans de référence
Pour chaque page listée plus haut, fournir un écran de référence composé avec les Twig Components :
1. Calendrier : mois avec des jours cochés dans différents états et le récapitulatif.
2. CRA imprimable : infos du freelance, infos du client, nom de la mission, mois concerné, tableau des jours travaillés, total, zone de signature pour les deux parties.
    - À l'écran : aperçu du document façon feuille A4 centrée, avec une barre d'actions (retour, bouton d'impression) et une courte aide expliquant comment enregistrer en PDF et décocher les en-têtes et pieds de page du navigateur.
    - À l'impression : feuille de style dédiée (`@media print` et `@page` en A4 portrait) qui masque tout sauf le document (layout, navigation, boutons, aide), force le thème clair, garde un rendu lisible en noir et blanc, évite les coupures au milieu d'une ligne du tableau ou de la zone de signature, et reste compatible avec Chrome, Firefox et Safari.
    - Définir un `<title>` explicite, car il sert de nom de fichier PDF par défaut (ex. `CRA - Client - Septembre 2026`).
3. Clients : liste avec plusieurs clients, liste vide, formulaire de création/édition (avec un exemple d'erreur de validation), modale de confirmation de suppression.
4. Profil : formulaire des infos du freelance.

## Livrable : un skill Claude
Produis le design system sous forme d'un dossier de skill avec cette structure :

cra-design-system/
├── SKILL.md
├── tokens/
│   ├── tokens.json                  # design tokens primitifs et sémantiques (source de vérité)
│   └── tokens.css                   # mêmes design tokens exposés en variables CSS (custom properties), thèmes clair et sombre
├── references/
│   ├── foundations.md               # liste des design tokens avec leur rôle : couleurs, typo, espacements, rayons, élévations, breakpoints
│   ├── components.md                # chaque composant : anatomie, props, variantes, états, tokens sémantiques utilisés, règles d'usage, à faire / à éviter
│   ├── calendar.md                  # spécification détaillée du calendrier (états, interactions, clavier, accessibilité)
│   ├── print.md                     # règles de la page imprimable du CRA (A4, marges, typo d'impression, structure du tableau, sauts de page, compatibilité navigateurs)
│   ├── screens.md                   # description de chaque page et de sa composition
│   └── symfony-integration.md       # conventions Twig Components, Stimulus, Turbo, form theme, UX Icons, organisation des fichiers CSS avec AssetMapper
└── templates/                       # fichiers prêts à copier dans le projet Symfony, en respectant son arborescence
├── assets/
│   ├── styles/
│   │   ├── tokens.css
│   │   ├── base.css             # reset, typographie de base, thèmes
│   │   ├── print.css            # feuille de style d'impression
│   │   └── components/*.css     # un fichier CSS par composant
│   └── controllers/*_controller.js   # contrôleurs Stimulus (modale, toast, calendrier, thème, impression…)
└── templates/
├── base.html.twig           # layout commun
├── components/*.html.twig   # Twig Components anonymes
├── form/theme.html.twig     # form theme Symfony
└── examples/*.html.twig     # les écrans de référence de chaque page, composés avec les Twig Components

Exigences pour SKILL.md :
- Frontmatter YAML avec `name` et `description`. La description doit expliquer clairement quand Claude Code doit utiliser le skill (toute création ou modification d'interface, de template Twig, de composant, de style ou de la page imprimable de l'application CRA).
- Corps court : principes du design system, puis règles non négociables :
    - toujours utiliser les tokens sémantiques, jamais les tokens primitifs ni de valeurs en dur dans le CSS ;
    - si un besoin n'est couvert par aucun token sémantique, en créer un nouveau dans `tokens.json` et `tokens.css` plutôt que d'écrire une valeur en dur ;
    - toujours réutiliser les Twig Components existants avant d'écrire du HTML à la main ; créer un nouveau composant plutôt que dupliquer du markup ;
    - interactions uniquement via Stimulus (pas de JavaScript inline) ;
    - toute modification de la page du CRA doit être vérifiée aussi en aperçu d'impression ;
    - respecter tous les états des composants et les règles d'accessibilité.
- Index des fichiers de `references/` et `templates/` avec, pour chacun, quand le lire ou l'utiliser.
- Instructions écrites pour un agent de développement : précises, actionnables, sans ambiguïté.

Contraintes :
- Tout le contenu technique (noms de design tokens, classes CSS, props, noms de composants, code, commentaires) est en anglais. Les textes d'interface affichés à l'utilisateur sont en français, et passent par le composant Translation de Symfony (fournir les clés de traduction utilisées).
- Code compatible avec les dernières versions stables de Symfony et Symfony UX.
