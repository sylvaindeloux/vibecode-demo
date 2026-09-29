# Skill and plugin sources

`cra-design-system` is ours. Every other skill in this folder is a third-party skill copied as-is from upstream; the only added file is the upstream `LICENSE`. Do not edit a vendored skill: update it from upstream instead (see below).

When a skill conflicts with `CLAUDE.md` or `cra-design-system`, those two win (rule in `CLAUDE.md`).

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

### Updating

1. Clone <https://github.com/smnandre/symfony-ux-skills> and check out the new tag.
2. Review the diff since the commit above: still Markdown only? New conflicts with `cra-design-system`?
3. Replace each `.claude/skills/<skill>/` with `skills/<skill>/` from the tag, and copy the upstream `LICENSE` into it.
4. Update the table above.

## Plugins

Enabled for everyone in `.claude/settings.json`. Plugins only load in local sessions: cloud sessions do not install the plugins a repository declares.

| Plugin | Marketplace | Version | License |
|---|---|---|---|
| [`php-lsp`](https://github.com/anthropics/claude-plugins-official/tree/main/plugins/php-lsp) | `claude-plugins-official` (Anthropic) | 1.0.0 (marketplace commit `fbe07fb`, 2026-09-28) | Apache-2.0 |

- Gives Claude PHP code intelligence and diagnostics through the Intelephense language server.
- Each developer installs the server once: `npm install -g intelephense` (1.18.5 at the time of writing).
- The plugin follows the official marketplace, which updates automatically.

## Evaluated, not installed (2026-09-29)

| Candidate | Why not, for now |
|---|---|
| Symfony AI Mate (`symfony/ai-mate`) | Composer package, so only once the Symfony app exists; still 0.x; rewrites a block of `CLAUDE.md`. To reconsider after the bootstrap. |
| `claude-security` (Anthropic) | Proprietary license (plugin only) and heavy token use. To reconsider before going to production. |
| Modern Web Guidance (Google Chrome) | Preview; declares itself mandatory for any HTML/CSS task; runs `npx modern-web-guidance@latest` on each use. |
| `symfony-skills` (Johannes Wachter) | Prototype, not yet an official Symfony repository. |
| Playwright MCP (Microsoft) | Declined. |
| Tailwind CSS skills | No official skill, and the app does not use Tailwind (native CSS design system). |
| `security-guidance`, `code-review`, `code-simplifier`, `pr-review-toolkit`, `commit-commands`, `feature-dev`, `frontend-design`, `webapp-testing`, `skill-creator` (Anthropic) | Duplicate built-in commands (`/security-review`, `/code-review`, `/simplify`, `/verify`) or conflict with the workflow in `CLAUDE.md` or with `cra-design-system`. |
| Context7 (Upstash), Axe Accessibility (Deque), Semgrep Guardian, GitHub MCP | Not from the technology's maintainers, paid subscription required, unauditable binaries, or duplicate of `gh`. |
