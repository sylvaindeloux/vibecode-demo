## Development workflow

You work on EasyCRA with me (Sylvain) through a strict loop. Follow it exactly.

### The loop

1. **Request** – I describe what I want.
2. **Analysis** – You read the relevant code and specs, then either:
    - ask me your questions (all at once, numbered, each with the options you see and their trade-offs), or
    - tell me in one or two sentences that everything is clear and start.
3. **Answers** – I answer. Ask again if something is still unclear; never guess.
4. **Code** – You implement on a dedicated branch created from an up-to-date `master`.
5. **Pull request** – You push the branch and open a PR on GitHub (`gh pr create`) against `master`, with a clear description: what, why, how to test, and anything I must check.
6. **Review** – I review and leave comments on the PR.
7. **Fixes** – You address every comment (read them with `gh pr view --comments` / `gh api`), push the fixes to the same branch, and reply to each comment saying what you changed.
8. **Merge** – I merge into `master`. You never merge, and you never push to `master`.
9. **Sync** – Once I tell you it is merged, you `git checkout master && git pull` before starting anything else.

Then we start again at step 1.

### Several topics or branches

If a request covers several topics, or needs more than one branch or PR (stacked or independent), **tell me before you start coding**: list the branches you plan, what each one contains, and how they depend on each other. Wait for my go.

When everything is done, give me:
- what each PR does (one or two lines each, with its link);
- the **exact order to merge them**, and whether I must rebase or retarget anything between two merges.

### Communication

- While you work, stay silent: no progress updates, no running commentary.
- When you are done, send **one final report**:
    - what you did (short);
    - the PR link(s) and, if there are several, the merge order;
    - the problems you hit and how you handled them;
    - anything left open or anything I should check.

### Decisions

- **Never make a technical or business decision on your own.** That covers architecture, libraries, data model, naming of public concepts, UX behaviour, business rules, edge cases, scope, and anything the request does not specify.
- When such a decision comes up, stop and ask me, with the options and your recommendation. If you are already coding, stop, ask, and wait.
- Following an existing convention of the codebase, or an explicit rule of this file or of a skill (e.g. `cra-design-system`), is not a decision: just apply it.

### Language

- **Always answer me in French**: questions, final reports, PR descriptions, replies to review comments.
- Everything in the code stays in English: identifiers, comments, commit messages, branch names, technical docs.

### Skills

- Project skills live in `.claude/skills/`. Third-party skills are copied as-is from upstream; their source, version and license are listed in `.claude/skills/SOURCES.md`.
- **When a skill conflicts with this file or with `cra-design-system`, this file and `cra-design-system` win.** Example: the Symfony UX skills show utility classes (`w-4 h-4`) and CSS class toggling (`classList`); here, styles use the design system's semantic tokens and Stimulus controllers only toggle attributes.
