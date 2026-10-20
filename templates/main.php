<?php
/**
 * @var callable $e
 * @var \PhpLives\Site $site
 * @var list<\PhpLives\Entry> $entries
 * @var list<\PhpLives\Entry> $featured The entries on full labels, the newest parcel first.
 * @var string $latestTitle
 * @var string $latestNote
 * @var list<\PhpLives\Bundle> $bundles Every other entry, by week and by month.
 * @var string $cutoff
 * @var string $stacks
 * @var list<\PhpLives\Post> $posts
 * @var string $copyScript
 * @var array<string, int> $counts
 * @var string $manifestScript
 * @var string $today
 */
use PhpLives\Bundle;
use PhpLives\DevNull;
use PhpLives\Format;
?>
  <div class="wrap">
    <section class="hero">
      <div>
        <h1><span>PHP delivers.</span> <span class="stuck">PHP lives!</span></h1>
        <p class="lede">A manifest of real things people shipped with PHP in the last 6 months. Every project links to its own source.</p>
        <div class="send">
          <a class="tag" href="https://github.com/albertoarena/phplives.dev/blob/main/CONTRIBUTING.md"><svg class="gh" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6.766 11.328c-2.063-.25-3.516-1.734-3.516-3.656 0-.781.281-1.625.75-2.188-.203-.515-.172-1.609.063-2.062.625-.078 1.468.25 1.968.703.594-.187 1.219-.281 1.985-.281.765 0 1.39.094 1.953.265.484-.437 1.344-.765 1.969-.687.218.422.25 1.515.046 2.047.5.593.766 1.39.766 2.203 0 1.922-1.453 3.375-3.547 3.64.531.344.89 1.094.89 1.954v1.625c0 .468.391.734.86.547C13.781 14.359 16 11.53 16 8.03 16 3.61 12.406 0 7.984 0 3.563 0 0 3.61 0 8.031a7.88 7.88 0 0 0 5.172 7.422c.422.156.828-.125.828-.547v-1.25c-.219.094-.5.156-.75.156-1.031 0-1.64-.562-2.078-1.609-.172-.422-.36-.672-.719-.719-.187-.015-.25-.093-.25-.187 0-.188.313-.328.625-.328.453 0 .844.281 1.25.86.313.452.64.655 1.031.655s.641-.14 1-.5c.266-.265.47-.5.657-.656"/></svg>Add your project</a>
          <a class="alt" href="https://github.com/albertoarena/phplives.dev/issues/new?template=suggest-a-project.yml">or suggest one in an issue</a>
        </div>
        <p class="send-note">Open source on GitHub. Anyone can send a project, and the editor checks each one.</p>      </div>
      <aside class="slip" aria-label="Manifest summary">
        <div class="slip-head">
          <h2>Manifest</h2>
          <span class="mono"><?= $e($today) ?></span>
        </div>
        <dl>
          <div><dt>Parcels</dt><dd class="mono"><?= count($entries) ?></dd></div>
          <div><dt>Shipped since</dt><dd class="mono"><?= $e($cutoff) ?></dd></div>
<?php if ($entries): ?>
          <div><dt>Last shipped</dt><dd class="mono"><time datetime="<?= $entries[0]->shipped->format('Y-m-d') ?>"><?= $e(Format::date($entries[0]->shipped)) ?></time></dd></div>
          <div><dt>Stacks</dt><dd><?= $e($stacks) ?></dd></div>
<?php endif ?>
        </dl>
        <p class="slip-foot">Each date comes from the project's own source.</p>
      </aside>
    </section>

    <section id="manifest" aria-labelledby="manifest-title">
      <div class="sec-head">
        <div>
          <h2 id="manifest-title">Shipped since <?= $e($cutoff) ?></h2>
          <p>Newest first. <span data-filter-status data-total="<?= count($entries) ?>" role="status"></span></p>
        </div>
<?php if ($entries): ?>
        <div class="sec-tools" data-tools hidden>
<?= $site->filterControl($counts) ?>
          <div class="view" role="group" aria-label="View">
            <button type="button" data-view-choice="labels" aria-pressed="true">Labels</button>
            <button type="button" data-view-choice="list" aria-pressed="false">List</button>
          </div>
        </div>
<?php endif ?>
      </div>
<?php if ($entries): ?>
      <div class="bundles" data-view="labels">
        <section class="bundle-new" id="this-week" data-group aria-labelledby="this-week-title">
          <div class="bundle-head">
            <h3 id="this-week-title"><?= $e($latestTitle) ?></h3>
            <p class="bundle-note"><?= $e($latestNote) ?></p>
          </div>
          <div class="grid">
<?php $heading = 'h4'; foreach ($featured as $i => $entry) {
    require __DIR__.'/entry.php';
} ?>
          </div>
        </section>
<?php foreach ($bundles as $b => $bundle): $n = count($bundle->entries); ?>
<?php if ($bundle->kind === 'week' && ($bundles[$b - 1] ?? null)?->kind !== 'week'): ?>
        <div class="weeks">
<?php endif ?>
        <section class="bundle bundle-<?= $e($bundle->kind) ?><?= $bundle->kind === 'week' ? ' span-'.min($n, 4) : '' ?>" id="<?= $e($bundle->id) ?>" data-group aria-labelledby="<?= $e($bundle->id) ?>-title">
          <div class="bundle-head">
            <h3 id="<?= $e($bundle->id) ?>-title"><?= $e($bundle->title) ?></h3>
            <span class="bundle-count mono" data-group-count><?= $n ?> <?= $n === 1 ? 'parcel' : 'parcels' ?></span>
          </div>
          <div class="stubs">
<?php foreach ($bundle->entries as $j => $entry) {
    require __DIR__.'/stub.php';
} ?>
          </div>
        </section>
<?php if ($bundle->kind === 'week' && ($bundles[$b + 1] ?? null)?->kind !== 'week'): ?>
        </div>
<?php endif ?>
<?php endforeach ?>
      </div>
      <section class="sheet list-sheet" data-view="list" aria-label="Manifest list" hidden>
<?php
$groups = [new Bundle('this-week', 'latest', $latestTitle, $featured), ...$bundles];
$newest = $featured[0]->slug;
$groupPrefix = 'list-';
require __DIR__.'/table.php';
?>
        <div class="sheet-foot"><span>Each date comes from the project's own source.</span></div>
      </section>
<?php else: ?>
      <p class="empty">Nothing has shipped in the last 6 months yet.</p>
<?php endif ?>
    </section>
  </div>

  <section class="dn" id="devnull" aria-labelledby="dn-title">
    <div class="wrap dn-grid<?= $posts ? '' : ' dn-solo' ?>">
      <div>
        <h2 id="dn-title">Shipped something in PHP?</h2>
        <p class="cta-p">Commit it on /dev/null, the social network for developers.</p>
        <a class="tag" href="https://dev-null.sh">Commit it on /dev/null
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></svg>
        </a>
        <div class="cli">
          <p class="cli-cap">Or from your terminal:</p>
          <div class="cli-line"><code><span aria-hidden="true">$ </span>curl -fsSL https://dev-null.sh/commit | bash</code><button type="button" data-copy="curl -fsSL https://dev-null.sh/commit | bash" data-copy-status="cli-status" hidden>Copy</button></div>
          <p class="cli-note">It asks for your message. You need a /dev/null account and an SSH key on your GitHub account.</p>
          <span class="sr-only" id="cli-status" role="status"></span>
        </div>
      </div>
<?php if ($posts): ?>
      <div class="strip">
        <h3>Dispatch log</h3>
        <p class="sub">Posts by @<?= $e(DevNull::ACCOUNT) ?> on /dev/null that link to phplives.dev.</p>
<?php foreach ($posts as $post): ?>
        <div class="post">
          <p class="meta"><a class="mono" href="<?= $e($post->url) ?>"><time datetime="<?= $post->posted->format('Y-m-d') ?>"><?= $e(Format::date($post->posted)) ?></time></a></p>
          <p class="body"><?= $e($post->body) ?></p>
        </div>
<?php endforeach ?>
        <p class="strip-foot"><a href="<?= $e(DevNull::URL.'/@'.DevNull::ACCOUNT) ?>">Follow @<?= $e(DevNull::ACCOUNT) ?> on /dev/null</a></p>
      </div>
<?php endif ?>
    </div>
    <figure class="dn-wall">
      <a href="<?= $e(DevNull::URL.'/@'.DevNull::ACCOUNT) ?>" aria-label="Follow @<?= $e(DevNull::ACCOUNT) ?> on /dev/null"><img src="/img/devnull-wallpaper-log-1920.webp" srcset="/img/devnull-wallpaper-log-1280.webp 1280w, /img/devnull-wallpaper-log-1920.webp 1920w" sizes="100vw" width="1920" height="1080" alt="" loading="lazy" decoding="async"></a>
      <figcaption class="wrap dn-wall-credit">Wallpaper from the <a href="https://dev-null.sh/brand#wallpapers">/dev/null brand kit</a>.</figcaption>
    </figure>
  </section>
  <script><?= $copyScript ?></script>
  <script><?= $manifestScript ?></script>
