# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

A small PHP build script, with no framework, turns entry files into static HTML.
Hosted on Cloudflare Workers static assets and deployed from GitHub Actions.

## Users

**First: PHP developers who want proof to share.** They want one link that shows
real, recent work in PHP. They open the page, skim it, and copy the URL.

Two other audiences, served second:
- Developers from other stacks, who arrive from that shared link.
- People shipping in PHP, who want their project listed and may send a PR. Once it
  is listed, they share its own page.

## Product Purpose

phplives.dev is a list of real things people shipped with PHP in the last 6 months.
It makes its point with evidence, not with an argument. It works when a
visitor can see, in a few seconds, that the list is real, recent and long.

## Positioning

whyphp.dev explains why modern PHP is good. phplives.dev shows that people are
shipping with it right now. Every entry links to its own public source. The site
never makes the case for PHP itself.

## Operating Context

- Visitors usually arrive from a link shared on X, dev/null or a chat, often on a
  phone (assumed, not yet confirmed).
- Anyone can suggest an entry with an issue, or send one as a pull request.
  Alberto is the editor: he checks each claim and merges.
- Each new entry is also posted to dev/null from Alberto's own account.

## Capabilities and Constraints

- The main page lists the last 6 months. The entries added last show as full
  labels. Every other entry is a stub, grouped by week and then by month.
- An archive lists the older entries, grouped by month.
- Each entry has its own page, with its full label, its details and its own share
  image.
- A Privacy page and a Credits page.
- One entry: name, one plain sentence, maker (with a dev/null link when found),
  shipped date as `DD/MM/YYYY`, the date it was added, source link, one stack tag.
  It may also have a short details text, a Composer package, a licence and a docs
  link.
- For feed readers, search engines and AI tools: an Atom feed (and one per stack),
  a sitemap, `robots.txt`, `llms.txt` and a Markdown copy of each entry.
- A strip of Alberto's dev/null commits that link to phplives.dev. It is hidden
  when the dev/null API is down.
- Static. No accounts, no database, no form, no cookies, no consent banner.
- Cloudflare Web Analytics only.
- HTTPS only, because `.dev` is HSTS preloaded.

## Brand Commitments

- The line is `PHP delivers. PHP lives!`, quoted exactly. Never write a variant.
- Simple English, for readers with English as a second language.
- No em dashes in any text.
- No attribution to any AI, anywhere.
- The site's name, line, art and copy are covered by `TRADEMARK.md`.

## Evidence on Hand

- Every entry is real, and links to its own public source.
- Never invent an entry, a maker, a date, a quote, a visitor count or a testimonial.

## Product Principles

1. **It shows, it does not argue.** The entries are the content.
2. **Every claim has a source.** If the project's own source does not say it, the
   site does not say it.
3. **Recent beats complete.** The main page is about the last 6 months.
4. **Easy to share.** The page must make its point in the first screen, on a phone.
5. **No one dominates.** Alberto's own projects may appear, but must not dominate.
