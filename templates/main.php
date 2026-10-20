<?php
/**
 * @var callable $e
 * @var \PhpLives\Site $site
 * @var list<\PhpLives\Entry> $entries
 * @var string $cutoff
 * @var string $stacks
 * @var list<\PhpLives\Post> $posts
 * @var string $copyScript
 * @var array<string, int> $counts
 * @var string $manifestScript
 * @var string $today
 */
use PhpLives\DevNull;
use PhpLives\Format;
?>
  <div class="wrap">
    <section class="hero">
      <div>
        <h1><span>PHP delivers.</span> <span class="stuck">PHP lives!</span></h1>
        <p class="lede">A manifest of real things people shipped with PHP in the last 6 months. Every project links to its own source.</p>
        <p class="open"><strong class="open-tag">Open source.</strong> Add your own project with a <a href="https://github.com/albertoarena/phplives.dev/blob/main/CONTRIBUTING.md">pull request</a>, or <a href="https://github.com/albertoarena/phplives.dev/issues/new?template=suggest-a-project.yml">suggest one in an issue</a>.</p>
      </div>
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
      <div class="grid" data-view="labels">
<?php foreach ($entries as $i => $entry) {
    require __DIR__.'/entry.php';
} ?>
      </div>
      <section class="sheet list-sheet" data-view="list" aria-label="Manifest list" hidden>
<?php $markNewest = true; require __DIR__.'/table.php'; ?>
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
