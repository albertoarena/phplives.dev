# Contributing

Thank you for helping. phplives.dev is a list of real things people shipped with
PHP. Most contributions are new entries, so this page is mostly about entries.

Alberto Arena is the editor. He checks every claim against the project's own
source, may rewrite the sentence, and decides what goes on the list. A suggestion
can be declined, even when it meets every rule below.

## What qualifies

A project qualifies when all three are true:

1. **It shipped in the last 6 months.** The date comes from the project's own
   source.
2. **A public link proves that it shipped.** Open source projects and closed
   products both count.
3. **PHP is the main language on its server side.**

**Which date counts:**

- The first public release.
- If the project has no releases: its first version tag.
- If it has neither: a launch post by the maker.
- A repo creation date alone is not enough.

A new **major** version (such as Pest 5) can be its own entry, dated by its
release. A minor or patch release does not qualify.

## Suggest a project

The easiest way is an issue. Use the
[suggestion form](https://github.com/albertoarena/phplives.dev/issues/new?template=suggest-a-project.yml)
and give the links. The editor checks it and, if it fits, writes the entry.

## Send an entry as a pull request

Add one file to `entries/`. Name it `YYYY-MM-DD-slug.php`. The date is the shipped
date, and the slug is lower case, with words joined by `-`.

```php
<?php

return [
    'name' => 'Laravel Vet',
    'sentence' => 'A dependency audit for PHP that shows you each package change before composer update writes it into vendor.',
    'makers' => [
        ['name' => 'Laravel', 'github' => 'laravel'],
    ],
    'shipped' => '2026-09-14',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/laravel/vet/releases/tag/v0.1.0',
    ],
    'source' => 'https://github.com/laravel/vet',
    'stack' => 'laravel',
    'devnull' => 'Shipped with PHP: Laravel Vet. A dependency audit for PHP that shows you each package change before composer update writes it into vendor. https://github.com/laravel/vet'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-18',
    'added' => '2026-10-19',
];
```

**The fields:**

| Field | What goes in it |
|---|---|
| `name` | The project's name, as the project writes it. |
| `sentence` | One plain sentence that says what the project does. 160 characters at most. |
| `makers` | One or more makers, each with a `name` and a `github` username. The first is the lead. Use a person or an organisation, as the project credits it. |
| `shipped` | The shipped date, as `YYYY-MM-DD`. See "Which date counts". |
| `proof` | The link that proves the date. `kind` is `release`, `tag` or `post`. |
| `source` | The project's own home: its repo, or its site for a closed product. |
| `stack` | One stack code (see below). |
| `devnull` | A draft of the post that announces the entry on /dev/null. The editor may rewrite it. |
| `checked` | The date you checked the facts, as `YYYY-MM-DD`. |
| `added` | The date the entry goes on the site, as `YYYY-MM-DD`. Use the day you send the pull request. The editor may change the date when he merges the pull request. The site's feed lists new entries by this date. |
| `details` | Optional. Two or three plain sentences that say more about the project. 400 characters at most. Say only what the project's own source says. |
| `package` | Optional. The Packagist name, such as `pestphp/pest`. The entry page then shows the `composer require` command. |
| `licence` | Optional. A short SPDX licence id, such as `MIT` or `Apache-2.0`, as the project's own source gives it. |
| `docs` | Optional. An HTTPS link to the project's docs. |

The entry page shows each optional field only when the entry gives it.

**Stack codes:** `laravel`, `symfony`, `nativephp`, `wordpress` or `php` (plain
PHP). A stack code names a framework or a platform, not a package. A project built
on a package goes under its framework: a Filament project goes under `laravel`. A
new code needs at least 2 entries. Until then, the entry goes under its parent stack or under
`php`.

### Rules for the text

- **Say only what the project's own source says.** Do not add a claim about the
  project or a person that its README, site or release post does not make.
- **Simple English.** Many readers use English as a second language. One idea per
  sentence.
- **No em dashes.** Use commas, colons or parentheses. The build fails on an em
  dash.
- **A version goes next to the product, never straight after PHP or a framework.**
  "Mollie API client for PHP 4.0" reads as PHP 4.0. Write "Mollie API client 4.0
  for PHP". Read each name with its version: if it could be read as a version of
  PHP, Laravel, Symfony, WordPress or NativePHP, rewrite it. The build fails on a
  name such as "for PHP 4.0".
- **Every link is HTTPS.**
- **Dates in prose are `DD/MM/YYYY`.** In the entry file, dates are `YYYY-MM-DD`.

### Check it before you send it

```sh
composer install
vendor/bin/pest
php bin/build
```

The build checks every entry file. It stops on a missing field, a bad date, an
unknown stack, a text that is too long, an em dash, a version straight after
PHP or a framework, or a link that is not HTTPS.
It also stops on a bad package name or licence id. On a pull
request, CI also checks that each link in the entry works.

## Changes to the code

Bug fixes and small improvements are welcome. For anything larger, open an issue
first, so we can agree on it before you build it.

- **Tests come first.** Write a failing test, then the code that makes it pass.
  PHP uses [Pest](https://pestphp.com) (`vendor/bin/pest`). The browser scripts
  use [Vitest](https://vitest.dev) (`nvm use && npm ci && npm test`).
- **Commit subject:** short and imperative, under about 50 characters ("Add the
  Laravel Vet entry", not "Added entry").
- **Keep to the design.** [`DESIGN.md`](DESIGN.md) describes it.

## Your project is listed and you want a change

A maker can always ask to fix or remove their entry. Open an issue, or send a
pull request that changes the entry file.

## Licence

By sending code, you agree that it is released under the [MIT licence](LICENSE)
of this repo. By sending an entry, you agree that the editor may publish its text
on the site and change it. The site's name, line, art and copy are covered by
[`TRADEMARK.md`](TRADEMARK.md).
