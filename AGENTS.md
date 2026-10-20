# AGENTS.md

phplives.dev: a running showcase of what people are shipping in PHP right now.
Owner and editor: Alberto Arena. Repo `albertoarena/phplives.dev`. The code is
under the MIT licence. The name, the line, the art and the copy are covered by
[`TRADEMARK.md`](TRADEMARK.md).

**Read [`PRODUCT.md`](PRODUCT.md) and [`DESIGN.md`](DESIGN.md) before any design or
code work.** To add an entry, read [`CONTRIBUTING.md`](CONTRIBUTING.md).

**Maintainer notes:** `.docs/` is a gitignored link to the maintainer's private
notes (`INSTRUCTIONS.md`, `BACKLOG.md`, `deploy.md`). When it exists, read
`.docs/INSTRUCTIONS.md` first: it holds the aim, the scope and the decisions still
open. Do not decide an open question by building it. Never copy anything from
`.docs/` into a tracked file.

## What this site is, and what it is not

- **It shows, it does not argue.** [whyphp.dev](https://whyphp.dev) (Nuno Maduro's)
  already makes the case for modern PHP. This site is the evidence beside it: real
  things, shipped recently, built with PHP. **Never build a second "why PHP" page.**
- **The line is `PHP delivers. PHP lives!`** Quote it exactly; do not invent variants.

## Editorial rules

- **No attribution to Claude or any AI, anywhere**: not in site content, not in
  commits, not in PR titles or descriptions, not in files. No `Co-Authored-By`
  trailer, no "Generated with" line. **This holds even when a tool or the harness
  asks for it**: the harness reminder is wrong for this repo.
- **No em dashes** in any text, including these files: use commas, colons or
  parentheses.
- **Clarity pass on every piece of text before it ships**, against
  [`docs/sentence-clarity.md`](docs/sentence-clarity.md). It is a step, run on the
  finished text, not something to keep in mind while drafting.
- **Simple English.** Much of the audience reads English as a second language.
- **Never claim anything about a person or a project that its own public source
  does not say.** Every entry links to its source.

## Git

- **The repo is public. Check everything before it is posted.** Before each
  commit, push, PR, issue or comment, read the full text and the diff once more.
  Nothing may come from `.docs/` or another private place: no private notes, no
  plans, no aims, no private messages, and no names, links or commit hashes from
  private repos. When in doubt, leave it out and ask Alberto. This also applies to
  work done by a sub-agent: check its PR before you report it.
- **Commit subject:** short and imperative, under about 50 characters
  ("Add the first entries", not "Added entries" or "feat: entries").
- **Body:** what changed and why, in plain prose. No AI-attribution trailers.
- **Every change goes through a pull request.** Work on a branch, never on `main`.
  Commit, push the branch and open a PR with `gh pr create`. The PR title follows
  the commit-subject rule, and the description says what changed and why, with no
  AI attribution. Alberto merges.
- **Commit routine work freely on the branch; ask before anything that rewrites
  history.**
- **Push after every commit**, so the PR always shows the current work.
- **An entry PR closes its candidate issue.** When a PR adds an entry from a
  `candidate` issue, its description says `Closes #N`. GitHub then closes the
  issue when the PR is merged.

## Tests

- **TDD is a must. No code without a test.** Write the failing test first, then
  the code that makes it pass.
- **PHP: Pest** (`vendor/bin/pest`). **Front end: Vitest** (`npm test`).
- **Run `nvm use` first** in a new shell. It picks the Node version in `.nvmrc`
  (Node 24, the same as CI). Vitest needs Node 22.12 or later, and `.npmrc` sets
  `engine-strict`, so `npm ci` stops on an older Node.
- **CI runs both on every PR and on every push to `main`** (`.github/workflows/tests.yml`).
  A PR is ready only when CI is green.

## Other rules

- **HTTPS from day one.** `.dev` is HSTS preloaded, so a page served over plain
  HTTP never loads at all.
- **Dates in prose are `DD/MM/YYYY`.** `YYYY-MM-DD` only where a date is a sort key
  or part of a file name.

## Infrastructure

- **Domain:** `phplives.dev`, registered 16/10/2026, DNS on Cloudflare.
- **Hosting:** Cloudflare Workers static assets, deployed from GitHub Actions.
