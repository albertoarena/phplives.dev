<?php
/**
 * @var callable $e
 * @var string $title
 * @var string|null $shareTitle The title on share cards, when it differs from the page title.
 * @var string|null $path Null on the page for missing addresses.
 * @var string $themeScript
 * @var string $analyticsToken
 * @var string $content
 * @var string|null $description The page's own description. Null for the site's.
 * @var string|null $markdown The address of the page's Markdown copy, when it has one.
 * @var string|null $image The page's own share card, with $imageAlt. Null for the site's.
 * @var string|null $imageAlt
 */
$nav = ['/' => 'Manifest', '/archive/' => 'Archive'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php $description ??= 'A manifest of real things people shipped with PHP in the last 6 months. Each one links to its own source and shows the date it shipped.'; ?>
<title><?= $e($title) ?></title>
<meta name="description" content="<?= $e($description) ?>">
<meta name="author" content="Alberto Arena">
<?php if ($path === null): ?>
<meta name="robots" content="noindex">
<?php else: ?>
<link rel="canonical" href="https://phplives.dev<?= $e($path) ?>">
<?php endif ?>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="me" href="https://phpc.social/@albertoarena">
<link rel="alternate" type="application/atom+xml" title="phplives.dev" href="/feed.xml">
<?php if ($markdown !== null): ?>
<link rel="alternate" type="text/markdown" href="<?= $e($markdown) ?>">
<?php endif ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="phplives.dev">
<meta property="og:title" content="<?= $e($shareTitle ?? $title) ?>">
<meta property="og:description" content="<?= $e($description) ?>">
<?php if ($path !== null): ?>
<meta property="og:url" content="https://phplives.dev<?= $e($path) ?>">
<?php endif ?>
<meta property="og:image" content="<?= $e($image ?? 'https://phplives.dev/share.png?v=3') ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= $e($imageAlt ?? 'PHP delivers. PHP lives! Real things people shipped with PHP in the last 6 months. phplives.dev') ?>">
<meta name="twitter:card" content="summary_large_image">
<script><?= $themeScript ?></script>
<link rel="preload" href="/fonts/archivo-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/site.css">
</head>
<body>
<div class="pl">
  <div class="wrap">
    <header class="top">
      <a class="mark" href="/">phplives.dev</a>
      <nav class="nav" aria-label="Main">
<?php foreach ($nav as $href => $label): ?>
        <a href="<?= $e($href) ?>"<?= $href === $path ? ' aria-current="page"' : '' ?>><?= $e($label) ?></a>
<?php endforeach ?>
        <a class="wide" href="/#devnull">Commit it on /dev/null</a>
      </nav>
      <div class="theme" role="group" aria-label="Colour theme" hidden>
        <button type="button" data-theme-choice="light" aria-pressed="false">Light</button>
        <button type="button" data-theme-choice="dark" aria-pressed="false">Dark</button>
        <button type="button" data-theme-choice="system" aria-pressed="true">System</button>
      </div>
    </header>
  </div>
<?= $content ?>
  <footer class="wrap foot">
    <div class="foot-open">
      <div>
        <h2>Open source. Anyone can help.</h2>
        <p>Add a project, fix a bug or improve the code. Every change is a pull request on GitHub.</p>
      </div>
      <div class="foot-open-links">
        <a class="tag" href="https://github.com/albertoarena/phplives.dev"><svg class="gh" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6.766 11.328c-2.063-.25-3.516-1.734-3.516-3.656 0-.781.281-1.625.75-2.188-.203-.515-.172-1.609.063-2.062.625-.078 1.468.25 1.968.703.594-.187 1.219-.281 1.985-.281.765 0 1.39.094 1.953.265.484-.437 1.344-.765 1.969-.687.218.422.25 1.515.046 2.047.5.593.766 1.39.766 2.203 0 1.922-1.453 3.375-3.547 3.64.531.344.89 1.094.89 1.954v1.625c0 .468.391.734.86.547C13.781 14.359 16 11.53 16 8.03 16 3.61 12.406 0 7.984 0 3.563 0 0 3.61 0 8.031a7.88 7.88 0 0 0 5.172 7.422c.422.156.828-.125.828-.547v-1.25c-.219.094-.5.156-.75.156-1.031 0-1.64-.562-2.078-1.609-.172-.422-.36-.672-.719-.719-.187-.015-.25-.093-.25-.187 0-.188.313-.328.625-.328.453 0 .844.281 1.25.86.313.452.64.655 1.031.655s.641-.14 1-.5c.266-.265.47-.5.657-.656"/></svg>See the code on GitHub</a>
        <a class="alt" href="https://github.com/albertoarena/phplives.dev/blob/main/CONTRIBUTING.md">Read how to contribute</a>
      </div>
    </div>
    <div class="foot-grid">
      <p>Made by <a href="https://albertoarena.it/?utm_source=phplives.dev&amp;utm_medium=footer">Alberto Arena</a>, author and main maintainer.</p>
      <p class="links"><a href="https://cogway.dev/?utm_source=phplives.dev&amp;utm_medium=footer">cogway.dev</a><a href="https://whyphp.dev">Why PHP? Read whyphp.dev</a></p>
    </div>
    <p class="fine">Every entry links to its own source. Dates are DD/MM/YYYY.</p>
    <p class="fine indie">phplives.dev is an independent site. /dev/null does not sponsor or endorse it.</p>
    <p class="fine foot-pages"><a href="/privacy/"<?= $path === '/privacy/' ? ' aria-current="page"' : '' ?>>Privacy</a><span aria-hidden="true">·</span><a href="/credits/"<?= $path === '/credits/' ? ' aria-current="page"' : '' ?>>Credits</a><span aria-hidden="true">·</span><a href="/feeds/"<?= $path === '/feeds/' ? ' aria-current="page"' : '' ?>>Feeds</a></p>
  </footer>
</div>
<script type="module" src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon='<?= $e(json_encode(['token' => $analyticsToken])) ?>'></script>
</body>
</html>
