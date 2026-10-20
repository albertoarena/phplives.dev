<?php
/**
 * @var callable $e
 *
 * Each credit says only what the item's own site or repo says. Leave out a maker
 * or a licence that the source does not state.
 */
$groups = [
    'fonts' => [
        'title' => 'Fonts',
        'note' => 'This site serves both fonts itself, with their licence files in /fonts/. The build draws the share images with fixed copies of both fonts. These copies are in assets/fonts/card/ in the code of the site.',
        'items' => [
            ['name' => 'Archivo', 'url' => 'https://fonts.google.com/specimen/Archivo', 'use' => 'The main typeface of the site, and of the share image of each entry.', 'from' => 'Omnibus-Type', 'licence' => 'SIL Open Font License 1.1'],
            ['name' => 'JetBrains Mono', 'url' => 'https://fonts.google.com/specimen/JetBrains+Mono', 'use' => 'The typeface for dates, numbers and code, on the site and on the share images.', 'from' => 'JetBrains, Philipp Nurullin and Konstantin Bulenkov', 'licence' => 'SIL Open Font License 1.1'],
        ],
    ],
    'icons' => [
        'title' => 'Icons',
        'items' => [
            ['name' => 'Octicons', 'url' => 'https://primer.style/octicons/', 'use' => 'The GitHub mark on the links to the code of this site.', 'from' => 'GitHub', 'licence' => 'MIT'],
        ],
    ],
    'build' => [
        'title' => 'Build and hosting',
        'items' => [
            ['name' => 'PHP', 'url' => 'https://www.php.net', 'use' => 'Runs the build script. The script turns the entry files into static HTML, with no framework. It draws the share image of each entry with the GD extension of PHP.', 'from' => 'The PHP Group and contributors'],
            ['name' => 'fontTools', 'url' => 'https://github.com/fonttools/fonttools', 'use' => 'Makes the fixed copies of the fonts that the build uses for the share images.', 'from' => 'Just van Rossum and contributors', 'licence' => 'MIT'],
            ['name' => 'Cloudflare Workers static assets', 'url' => 'https://developers.cloudflare.com/workers/static-assets/', 'use' => 'Hosts the site.', 'from' => 'Cloudflare'],
            ['name' => 'Wrangler', 'url' => 'https://developers.cloudflare.com/workers/wrangler/', 'use' => 'Deploys the site to Cloudflare.', 'from' => 'Cloudflare', 'licence' => 'MIT or Apache 2.0'],
            ['name' => 'GitHub Actions', 'url' => 'https://github.com/features/actions', 'use' => 'Runs the tests. It also builds and deploys the site.', 'from' => 'GitHub'],
            ['name' => 'Cloudflare Web Analytics', 'url' => 'https://www.cloudflare.com/web-analytics/', 'use' => 'Counts the visits to the site.', 'from' => 'Cloudflare'],
        ],
    ],
    'tests' => [
        'title' => 'Tests',
        'items' => [
            ['name' => 'Pest', 'url' => 'https://pestphp.com', 'use' => 'Tests the PHP code.', 'from' => 'Nuno Maduro', 'licence' => 'MIT'],
            ['name' => 'Vitest', 'url' => 'https://vitest.dev', 'use' => 'Tests the scripts that run in the browser.', 'from' => 'VoidZero and the Vitest contributors', 'licence' => 'MIT'],
        ],
    ],
    'data' => [
        'title' => 'Data',
        'items' => [
            ['name' => '/dev/null', 'url' => 'https://dev-null.sh', 'use' => 'The build reads the public /dev/null API. The API says which makers have a /dev/null profile. The API also gives the posts that link to phplives.dev. The terminal command on the main page is the /dev/null script.', 'from' => 'Nuno Maduro', 'code' => 'https://github.com/nunomaduro/dev-null.sh'],
            ['name' => '/dev/null wallpaper', 'url' => 'https://dev-null.sh/brand#wallpapers', 'use' => 'The picture at the end of the /dev/null section on the main page. It comes from the /dev/null brand kit, as it is.', 'from' => '/dev/null'],
        ],
    ],
];
$count = array_sum(array_map(fn (array $group) => count($group['items']), $groups));
?>
  <div class="wrap">
    <section class="head">
      <div>
        <h1>Credits</h1>
        <p>This site is built on the work of other people. This page lists the fonts, software, services and data that the site uses.</p>
      </div>
    </section>
    <section class="sheet credits" aria-labelledby="credits-title">
      <div class="sheet-head">
        <h2 id="credits-title">What the site uses</h2>
        <span class="mono"><?= $count ?> items</span>
      </div>
<?php foreach ($groups as $key => $group): ?>
      <section class="cr-group" id="credits-<?= $e($key) ?>" aria-labelledby="credits-<?= $e($key) ?>-title">
        <h3 class="cap" id="credits-<?= $e($key) ?>-title"><?= $e($group['title']) ?></h3>
<?php if (isset($group['note'])): ?>
        <p class="cr-note"><?= $e($group['note']) ?></p>
<?php endif ?>
        <ul class="cr-list">
<?php foreach ($group['items'] as $item): ?>
          <li>
            <p class="item"><a href="<?= $e($item['url']) ?>"><?= $e($item['name']) ?></a></p>
            <p class="use"><?= $e($item['use']) ?><?php if (isset($item['code'])): ?> <a href="<?= $e($item['code']) ?>">The code of /dev/null is on GitHub.</a><?php endif ?></p>
<?php if (isset($item['from']) || isset($item['licence'])): ?>
            <dl class="cr-meta">
<?php if (isset($item['from'])): ?>
              <div><dt>From</dt><dd class="cr-from"><?= $e($item['from']) ?></dd></div>
<?php endif ?>
<?php if (isset($item['licence'])): ?>
              <div><dt>Licence</dt><dd class="cr-lic"><?= $e($item['licence']) ?></dd></div>
<?php endif ?>
            </dl>
<?php endif ?>
          </li>
<?php endforeach ?>
        </ul>
      </section>
<?php endforeach ?>
      <div class="cr-end">
        <p>The names and logos of these projects belong to their owners.</p>
        <p>Is a credit missing or wrong? Tell Alberto through <a href="https://albertoarena.it/?utm_source=phplives.dev&amp;utm_medium=credits">albertoarena.it</a>.</p>
      </div>
      <div class="sheet-foot"><span>Each maker and licence comes from the project's own site or repo.</span><span class="updated">Last updated <span class="mono">20/10/2026</span></span></div>
    </section>
  </div>
