<?php
/**
 * @var callable $e
 * @var string $title
 * @var string|null $path Null on the page for missing addresses.
 * @var string $themeScript
 * @var string $analyticsToken
 * @var string $content
 */
$nav = ['/' => 'Manifest', '/archive/' => 'Archive'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php $description = 'A manifest of real things people shipped with PHP in the last 6 months. Each one links to its own source and shows the date it shipped.'; ?>
<title><?= $e($title) ?></title>
<meta name="description" content="<?= $e($description) ?>">
<meta name="author" content="Alberto Arena">
<?php if ($path === null): ?>
<meta name="robots" content="noindex">
<?php else: ?>
<link rel="canonical" href="https://phplives.dev<?= $e($path) ?>">
<?php endif ?>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<meta property="og:type" content="website">
<meta property="og:site_name" content="phplives.dev">
<meta property="og:title" content="<?= $e($title) ?>">
<meta property="og:description" content="<?= $e($description) ?>">
<?php if ($path !== null): ?>
<meta property="og:url" content="https://phplives.dev<?= $e($path) ?>">
<?php endif ?>
<meta property="og:image" content="https://phplives.dev/share.png?v=3">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="PHP delivers. PHP lives! Real things people shipped with PHP in the last 6 months. phplives.dev">
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
    <div class="foot-grid">
      <p>Made by <a href="https://albertoarena.it/?utm_source=phplives.dev&amp;utm_medium=footer">Alberto Arena</a>, author and main maintainer.</p>
      <p class="links"><a href="https://cogway.dev/?utm_source=phplives.dev&amp;utm_medium=footer">cogway.dev</a><a href="https://whyphp.dev">Why PHP? Read whyphp.dev</a></p>
    </div>
    <p class="fine">Every entry links to its own source. Dates are DD/MM/YYYY.</p>
    <p class="fine indie">phplives.dev is an independent site. /dev/null does not sponsor or endorse it.</p>
    <p class="fine foot-pages"><a href="/privacy/"<?= $path === '/privacy/' ? ' aria-current="page"' : '' ?>>Privacy</a><span aria-hidden="true">·</span><a href="/credits/"<?= $path === '/credits/' ? ' aria-current="page"' : '' ?>>Credits</a><span aria-hidden="true">·</span><span class="soon">Source code: public in the next few days</span></p>
  </footer>
</div>
<script type="module" src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon='<?= $e(json_encode(['token' => $analyticsToken])) ?>'></script>
</body>
</html>
