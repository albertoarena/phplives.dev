<?php
/**
 * One shipping label. The manifest grid and the entry page share it, so the two never drift apart.
 *
 * @var callable $e
 * @var \PhpLives\Site $site
 * @var \PhpLives\Entry $entry
 * @var int $i Its place on the page. The first label is the newest parcel.
 * @var string|null $heading The tag of the project name. h3 when it is not set.
 * @var bool|null $onEntryPage True on the entry's own page: the name is the page heading (h1), the newest
 *                             parcel is marked by $newest, and the label has no link to its page.
 * @var bool|null $newest On the entry page: true when this is the newest parcel.
 * @var string|null $newestSlug On the main page: the slug of the newest parcel. Without it, the first label is the newest.
 */
use PhpLives\Format;

$id = $e($entry->slug);
$tid = $site->trackingNumber($entry);
$x = 0;
$own = $onEntryPage ?? false;
$isNewest = $own ? ($newest ?? false) : (isset($newestSlug) ? $entry->slug === $newestSlug : $i === 0);
$heading = $own ? 'h1' : (in_array($heading ?? null, ['h2', 'h3', 'h4'], true) ? $heading : 'h3');
?>
        <article class="lab<?= $isNewest ? ' newest' : '' ?>" style="--i: <?= $i ?>" data-stack="<?= $e($entry->stack) ?>" aria-labelledby="t-<?= $id ?>">
<?php if ($isNewest): ?>
          <div class="lab-new">Newest parcel</div>
<?php endif ?>
          <div class="blk from">
            <div>
              <span class="cap">From</span>
              <ul class="makers">
<?php foreach ($entry->makers as $maker): ?>
                <li>
<?php if ($maker->github === null): ?>
                  <span class="who"><?= $e($maker->name) ?></span>
<?php else: ?>
                  <a class="who" href="https://github.com/<?= $e($maker->github) ?>"><?= $e($maker->name) ?></a>
<?php endif ?>
<?php if ($site->hasDevnull($maker)): ?>
                  <a class="devnull mono" href="https://dev-null.sh/@<?= $e($maker->github) ?>" aria-label="<?= $e($maker->name) ?> on /dev/null">/dev/null
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></svg>
                  </a>
<?php endif ?>
                </li>
<?php endforeach ?>
              </ul>
            </div>
            <div class="route" aria-label="Stack: <?= $e(Format::stackLabel($entry->stack)) ?>">
              <b aria-hidden="true"><?= $e(Format::stackCode($entry->stack)) ?></b>
              <span><?= $e(Format::stackLabel($entry->stack)) ?></span>
            </div>
          </div>
          <div class="blk contents">
            <span class="cap">Contents</span>
            <<?= $heading ?> id="t-<?= $id ?>"><?= $e($entry->name) ?></<?= $heading ?>>
            <p><?= $e($entry->sentence) ?></p>
          </div>
          <div class="blk ship">
            <span class="cap">Shipped</span>
            <p class="date mono"><time datetime="<?= $entry->shipped->format('Y-m-d') ?>"><?= $e(Format::date($entry->shipped)) ?></time></p>
            <p class="proof">Date from: <a href="<?= $e($entry->proofUrl) ?>"><?= $e(Format::proofLabel($entry->proofKind)) ?></a></p>
            <svg class="waves" viewBox="0 0 120 46" preserveAspectRatio="xMaxYMid slice" aria-hidden="true">
              <path d="M0 8 q7.5 -6 15 0 t15 0 t15 0 t15 0 t15 0 t15 0 t15 0 t15 0" fill="none" stroke="currentColor" stroke-width="2.4"/>
              <path d="M0 23 q7.5 -6 15 0 t15 0 t15 0 t15 0 t15 0 t15 0 t15 0 t15 0" fill="none" stroke="currentColor" stroke-width="2.4"/>
              <path d="M0 38 q7.5 -6 15 0 t15 0 t15 0 t15 0 t15 0 t15 0 t15 0 t15 0" fill="none" stroke="currentColor" stroke-width="2.4"/>
            </svg>
            <svg class="pm" style="--i: <?= $i ?>" viewBox="0 0 120 120" aria-hidden="true">
              <defs>
                <path id="pt-<?= $id ?>" d="M 18 60 A 42 42 0 0 1 102 60"/>
                <path id="pb-<?= $id ?>" d="M 14 60 A 46 46 0 0 0 106 60"/>
              </defs>
              <circle cx="60" cy="60" r="56" fill="none" stroke="currentColor" stroke-width="3"/>
              <circle cx="60" cy="60" r="34" fill="none" stroke="currentColor" stroke-width="1.6"/>
              <text fill="currentColor" font-family="Archivo, sans-serif" font-size="11.5" font-weight="800" letter-spacing="2.2"><textPath href="#pt-<?= $id ?>" startOffset="50%" text-anchor="middle">PHPLIVES.DEV</textPath></text>
              <text fill="currentColor" font-family="Archivo, sans-serif" font-size="11.5" font-weight="800" letter-spacing="2.6"><textPath href="#pb-<?= $id ?>" startOffset="50%" text-anchor="middle">SHIPPED</textPath></text>
              <text x="60" y="58" fill="currentColor" font-family="JetBrains Mono, monospace" font-size="12" font-weight="600" text-anchor="middle"><?= $entry->shipped->format('d/m') ?></text>
              <text x="60" y="73" fill="currentColor" font-family="JetBrains Mono, monospace" font-size="12" font-weight="600" text-anchor="middle"><?= $entry->shipped->format('Y') ?></text>
            </svg>
          </div>
          <div class="blk">
            <svg class="bars" viewBox="0 0 <?= array_sum(array_column(Format::barcode($tid), 'width')) * 1.8 ?> 46" preserveAspectRatio="none" aria-hidden="true">
<?php foreach (Format::barcode($tid) as $bar): $w = $bar['width'] * 1.8; ?>
<?php if ($bar['ink']): ?>              <rect x="<?= $x ?>" width="<?= $w ?>" height="46"/>
<?php endif; $x += $w; endforeach ?>
            </svg>
            <div class="track">
              <span class="cap">Track</span>
              <a href="<?= $e($entry->source) ?>"><span class="mono"><?= $e(Format::linkText($entry->source)) ?></span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></svg>
              </a>
            </div>
<?php if ($site->hasEntryPages() && ! $own): ?>
            <div class="lab-foot">
              <p class="tid mono"><?= $e($tid) ?></p>
              <a class="more" href="<?= $e($site::entryPath($entry)) ?>">Details<span class="sr-only"> for <?= $e($entry->name) ?></span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
              </a>
            </div>
<?php else: ?>
            <p class="tid mono"><?= $e($tid) ?></p>
<?php endif ?>
          </div>
        </article>
