<?php
/**
 * @var callable $e
 * @var list<\PhpLives\Entry> $entries
 * @var list<\PhpLives\Bundle> $bundles The entries, one group per month.
 * @var string $cutoff
 * @var string $today
 * @var \PhpLives\Site $site
 * @var array<string, int> $counts
 * @var string $manifestScript
 */
?>
  <div class="wrap">
    <section class="head">
      <div>
        <h1>Archive manifest</h1>
        <p>Projects that shipped more than 6 months ago. They stay on the site, newest first. <span data-filter-status data-total="<?= count($entries) ?>" role="status"></span></p>
      </div>
<?php if ($entries): ?>
      <div class="sec-tools" data-tools hidden>
<?= $site->filterControl($counts) ?>
      </div>
<?php endif ?>
    </section>
    <section class="sheet" id="archive" aria-labelledby="sheet-title">
      <div class="sheet-head">
        <h2 id="sheet-title">Shipped before <?= $e($cutoff) ?></h2>
        <span class="mono"><?= count($entries) ?> <?= count($entries) === 1 ? 'parcel' : 'parcels' ?></span>
      </div>
<?php if ($entries): ?>
<?php $groups = $bundles; $newest = null; $groupPrefix = ''; require __DIR__.'/table.php'; ?>
<?php else: ?>
      <p class="empty">No entry is older than 6 months yet.</p>
<?php endif ?>
      <div class="sheet-foot"><span>Each date comes from the project's own source.</span><span class="mono"><?= $e($today) ?></span></div>
    </section>
  </div>
  <script><?= $manifestScript ?></script>
