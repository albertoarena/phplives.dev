# phplives.dev

**PHP delivers. PHP lives!**

[phplives.dev](https://phplives.dev) is a list of real things people shipped with
PHP in the last 6 months. Each entry links to its own public source and shows the
date it shipped. After 6 months, an entry moves to the
[archive](https://phplives.dev/archive/). It stays on the site.

The site does not argue for PHP. It shows the work. For the case for modern PHP,
read [whyphp.dev](https://whyphp.dev).

## Suggest a project

Shipped something with PHP, or seen something that belongs here?

- **Open an issue** with the
  [suggestion form](https://github.com/albertoarena/phplives.dev/issues/new?template=suggest-a-project.yml).
- **Or send the entry yourself** as a pull request.

[`CONTRIBUTING.md`](CONTRIBUTING.md) says what qualifies and how an entry file
looks.

Is your project listed? Its entry page has a badge for your README.

## How it works

A small PHP build script, with no framework, turns one PHP file per entry into
static HTML. It also writes an Atom feed (and one per stack, all listed on
`/feeds/`), a sitemap, `robots.txt`, `llms.txt` and a Markdown copy of each entry. It draws a share card for each entry page with GD.
GitHub Actions runs the tests, builds the site and deploys it to Cloudflare Workers.
A daily build moves old entries to the archive.

```
entries/      one file per entry, named YYYY-MM-DD-slug.php
src/          the build: entries, checks, the /dev/null API
templates/    the pages
assets/       styles, scripts, fonts and images
art/          the icon, the share card and the badge (see TRADEMARK.md)
tests/        Pest (PHP) and Vitest (browser scripts)
bin/build     builds the site into dist/
bin/card-fonts makes the fonts of the share cards, in assets/fonts/card/
```

## Run it locally

You need PHP 8.4 or later and Composer. The share cards need the GD extension with
FreeType. Without it, the site still builds, and entry pages use the site's own share
card. For the browser tests, you also need Node 24 (see `.nvmrc`).

```sh
composer install
php bin/build
php -S 127.0.0.1:8765 -t dist
```

Then open http://127.0.0.1:8765/.

To run the tests:

```sh
vendor/bin/pest
nvm use && npm ci && npm test
```

## Licence

The code is free software under the [MIT licence](LICENSE). The name, the line,
the art, the design and the copy of the site are not part of that licence. See
[`TRADEMARK.md`](TRADEMARK.md).

Made by [Alberto Arena](https://albertoarena.it).
