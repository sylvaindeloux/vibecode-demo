# Skill sources

`cra-design-system`, `symfony-best-practices` and the `symfony-bp-*` skills are ours (the latter derived from the Symfony documentation, see below). Every other skill in this folder is a third-party skill copied as-is from upstream; the only added file is the upstream `LICENSE`. Do not edit a vendored skill: update it from upstream instead (see below).

When a skill conflicts with `CLAUDE.md` or `cra-design-system`, those two win (rule in `CLAUDE.md`). When an example of a vendored skill conflicts with a `symfony-bp-*` skill, the `symfony-bp-*` skill wins: vendored examples are illustrative (decision of 2026-09-29).

## Vendored skills

| Skill | Source | Version | Commit | Released | License |
|---|---|---|---|---|---|
| `symfony-ux` | [smnandre/symfony-ux-skills](https://github.com/smnandre/symfony-ux-skills/tree/v1.2.0/skills/symfony-ux) | v1.2.0 | `1e99301a6255724eca9c49ce9cdb8c241771ab05` | 2026-06-14 | MIT |
| `stimulus` | [smnandre/symfony-ux-skills](https://github.com/smnandre/symfony-ux-skills/tree/v1.2.0/skills/stimulus) | v1.2.0 | `1e99301a6255724eca9c49ce9cdb8c241771ab05` | 2026-06-14 | MIT |
| `turbo` | [smnandre/symfony-ux-skills](https://github.com/smnandre/symfony-ux-skills/tree/v1.2.0/skills/turbo) | v1.2.0 | `1e99301a6255724eca9c49ce9cdb8c241771ab05` | 2026-06-14 | MIT |
| `twig-component` | [smnandre/symfony-ux-skills](https://github.com/smnandre/symfony-ux-skills/tree/v1.2.0/skills/twig-component) | v1.2.0 | `1e99301a6255724eca9c49ce9cdb8c241771ab05` | 2026-06-14 | MIT |
| `live-component` | [smnandre/symfony-ux-skills](https://github.com/smnandre/symfony-ux-skills/tree/v1.2.0/skills/live-component) | v1.2.0 | `1e99301a6255724eca9c49ce9cdb8c241771ab05` | 2026-06-14 | MIT |
| `ux-icons` | [smnandre/symfony-ux-skills](https://github.com/smnandre/symfony-ux-skills/tree/v1.2.0/skills/ux-icons) | v1.2.0 | `1e99301a6255724eca9c49ce9cdb8c241771ab05` | 2026-06-14 | MIT |

- Author: Simon André, Symfony UX contributor. Announced on the Symfony blog: <https://symfony.com/blog/introducing-ai-skills-for-symfony-ux>. Personal repository, not under the `symfony` organization.
- Content: Markdown only, no scripts. Reviewed on 2026-09-29: no dangerous commands, no instructions that send data outside the project.
- Upstream targets Symfony UX 2.22 to 2.28+, while `cra-design-system` targets UX 3.x: when in doubt, check an API against the UX 3 documentation.
- Not copied: `ux-map` (no map in the app) and the upstream root files (`CLAUDE.md`, `AGENTS.md`, plugin manifests).
- Known conflicts, where `cra-design-system` wins:
  - examples with utility classes (`w-4 h-4`, `bg-blue-600`) instead of semantic tokens;
  - Stimulus examples that toggle CSS classes (`classList`, `static classes`) instead of attributes;
  - `twig-component` also triggers on "build a design system in Symfony": UI work follows `cra-design-system`.
- Known conflicts, where the `symfony-bp-*` skills win:
  - `turbo`, "Inline Editing" pattern (`references/patterns.md`): separate edit and update actions, data read with `$request->request->get()`, no form type and no validation. Follow `symfony-bp-forms`: form type class, constraints on the object, one action that renders and processes the form.
- Not a conflict: Twig Component file names in PascalCase and camelCase props (`twig-component`, `cra-design-system`) versus the snake_case rule of `symfony-bp-templates`, which exempts Twig Components in its "Project notes".

### Updating

1. Clone <https://github.com/smnandre/symfony-ux-skills> and check out the new tag.
2. Review the diff since the commit above: still Markdown only? New conflicts with `cra-design-system`?
3. Replace each `.claude/skills/<skill>/` with `skills/<skill>/` from the tag, and copy the upstream `LICENSE` into it.
4. Update the table above.

## Derived from the Symfony documentation

| Skills | Source | Version | Read on | License |
|---|---|---|---|---|
| `symfony-best-practices` (index), `symfony-bp-creating-the-project`, `symfony-bp-configuration`, `symfony-bp-business-logic`, `symfony-bp-controllers`, `symfony-bp-templates`, `symfony-bp-forms`, `symfony-bp-security`, `symfony-bp-web-assets`, `symfony-bp-tests` | [The Symfony Framework Best Practices](https://symfony.com/doc/current/best_practices.html), one skill per section; the pages it links to were read for current syntax | Symfony 8.1 docs | 2026-09-29 | [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/) |

- Rules are rephrased and examples written for this repository; each skill ends with a "Source" section: section URL, reading date, linked pages checked, license.
- Not covered: the "Internationalization" section (the project is not internationalized).
- Project-specific points live only in "Project notes" sections, validated before being added. Gaps between the official page and current Symfony are flagged as "Doc note".

### Updating

1. Re-read <https://symfony.com/doc/current/best_practices.html>, note the Symfony version it documents, and compare each section with its `symfony-bp-*` skill.
2. Re-check the examples against the linked pages listed in each skill's "Source" section.
3. Stay faithful to the page: no invented rule; project-specific points only in "Project notes", once validated.
4. Update the reading date and version in each skill and in the table above.

## Evaluated, not installed (2026-09-29)

| Candidate | Why not, for now |
|---|---|
| Symfony AI Mate (`symfony/ai-mate`) | Composer package, so only once the Symfony app exists; still 0.x; rewrites a block of `CLAUDE.md`. To reconsider after the bootstrap. |
| `claude-security` (Anthropic) | Proprietary license (plugin only) and heavy token use. To reconsider before going to production. |
| Modern Web Guidance (Google Chrome) | Preview; declares itself mandatory for any HTML/CSS task; runs `npx modern-web-guidance@latest` on each use. |
| `symfony-skills` (Johannes Wachter) | Prototype, not yet an official Symfony repository. |
| Playwright MCP (Microsoft) | Declined. |
| `php-lsp` (Anthropic) | Declined. |
| Tailwind CSS skills | No official skill, and the app does not use Tailwind (native CSS design system). |
| `security-guidance`, `code-review`, `code-simplifier`, `pr-review-toolkit`, `commit-commands`, `feature-dev`, `frontend-design`, `webapp-testing`, `skill-creator` (Anthropic) | Duplicate built-in commands (`/security-review`, `/code-review`, `/simplify`, `/verify`) or conflict with the workflow in `CLAUDE.md` or with `cra-design-system`. |
| Context7 (Upstash), Axe Accessibility (Deque), Semgrep Guardian, GitHub MCP | Not from the technology's maintainers, paid subscription required, unauditable binaries, or duplicate of `gh`. |
