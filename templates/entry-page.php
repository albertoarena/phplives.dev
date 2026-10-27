<?php
/**
 * One entry's own page: its label, large, on the depot shelf. The docket under it lists
 * the facts from the entry file. The parcels shipped just before and just after it show
 * as tear-off stubs.
 *
 * @var callable $e
 * @var \PhpLives\Site $site
 * @var \PhpLives\Entry $entry
 * @var bool $newest True when this is the newest parcel.
 * @var bool $archived True when the entry is in the archive.
 * @var \PhpLives\Entry|null $before The parcel shipped just before this one.
 * @var \PhpLives\Entry|null $after The parcel shipped just after this one.
 * @var string $url The page's own address.
 * @var string $shareTitle
 * @var string $badge The Markdown for the entry's README badge.
 * @var \PhpLives\QrCode $qr The QR code of the page.
 * @var bool $qrPng True when the QR code is also offered as a PNG file.
 * @var string $copyScript
 * @var string $shareScript
 * @var list<string> $filterTopics The topics the main page can filter by.
 */
use PhpLives\Format;
use PhpLives\QrCode;

$arrowOut = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></svg>';
$i = 0;
$onEntryPage = true;
?>
  <div class="wrap">
    <p class="back"><a href="<?= $archived ? '/archive/' : '/' ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 18-6-6 6-6"/></svg><?= $archived ? 'Back to the archive' : 'Back to the manifest' ?></a></p>
  </div>
  <div class="depot">
    <div class="shelf">
<?php require __DIR__.'/entry.php'; ?>
      <section class="docket" aria-labelledby="docket-title">
        <div class="sheet-head">
          <h2 id="docket-title">Parcel details</h2>
          <span class="mono"><?= $e($site->trackingNumber($entry)) ?></span>
        </div>
<?php if ($entry->details !== null): ?>
        <p class="dk-details"><?= $e($entry->details) ?></p>
<?php endif ?>
<?php if ($entry->package !== null): $command = 'composer require '.$entry->package; ?>
        <div class="dk-install">
          <span class="cap">Install</span>
          <div class="cli-line"><code><span aria-hidden="true">$ </span><?= $e($command) ?></code><button type="button" data-copy="<?= $e($command) ?>" data-copy-status="install-status" hidden>Copy</button></div>
          <span class="sr-only" id="install-status" role="status"></span>
        </div>
<?php endif ?>
        <dl class="dk-facts">
          <div>
            <dt>Made by</dt>
            <dd>
              <ul class="dk-makers">
<?php foreach ($entry->makers as $maker): ?>
                <li>
                  <span class="who"><?= $e($maker->name) ?></span>
<?php if ($maker->github !== null): ?>
                  <a class="mono" href="https://github.com/<?= $e($maker->github) ?>" aria-label="<?= $e($maker->name) ?> on GitHub">github.com/<?= $e($maker->github) ?><?= $arrowOut ?></a>
<?php endif ?>
<?php if ($site->hasDevnull($maker)): ?>
                  <a class="mono" href="https://dev-null.sh/@<?= $e($maker->github) ?>" aria-label="<?= $e($maker->name) ?> on /dev/null">/dev/null<?= $arrowOut ?></a>
<?php endif ?>
                </li>
<?php endforeach ?>
              </ul>
            </dd>
          </div>
          <div><dt>Source</dt><dd><a class="dk-link mono" href="<?= $e($entry->source) ?>"><?= $e(Format::linkText($entry->source)) ?><?= $arrowOut ?></a></dd></div>
<?php if ($entry->docs !== null): ?>
          <div><dt>Docs</dt><dd><a class="dk-link mono" href="<?= $e($entry->docs) ?>"><?= $e(Format::linkText($entry->docs)) ?><?= $arrowOut ?></a></dd></div>
<?php endif ?>
<?php if ($entry->licence !== null): ?>
          <div><dt>Licence</dt><dd class="dk-licence mono"><?= $e($entry->licence) ?></dd></div>
<?php endif ?>
<?php if ($entry->topics !== []): ?>
          <div>
            <dt>Topics</dt>
            <dd>
              <ul class="dk-topics">
<?php foreach ($entry->topics as $topic): ?>
<?php if (in_array($topic, $filterTopics, true)): ?>
                <li><a class="dk-topic" href="/#topic=<?= $e($topic) ?>" aria-label="More entries about <?= $e(Format::topicLabel($topic)) ?>"><?= $e(Format::topicLabel($topic)) ?></a></li>
<?php else: ?>
                <li><span class="dk-topic"><?= $e(Format::topicLabel($topic)) ?></span></li>
<?php endif ?>
<?php endforeach ?>
              </ul>
            </dd>
          </div>
<?php endif ?>
          <div><dt>Shipped</dt><dd class="mono"><time datetime="<?= $entry->shipped->format('Y-m-d') ?>"><?= $e(Format::date($entry->shipped)) ?></time></dd></div>
          <div><dt>Date from</dt><dd><a class="dk-link" href="<?= $e($entry->proofUrl) ?>"><?= $e(Format::proofLabel($entry->proofKind)) ?><?= $arrowOut ?></a></dd></div>
          <div><dt>Added</dt><dd class="mono"><time datetime="<?= $entry->added->format('Y-m-d') ?>"><?= $e(Format::date($entry->added)) ?></time></dd></div>
          <div><dt>Checked</dt><dd class="mono"><time datetime="<?= $entry->checked->format('Y-m-d') ?>"><?= $e(Format::date($entry->checked)) ?></time></dd></div>
        </dl>
        <div class="dk-share">
          <p><span class="cap">Share this parcel</span><span class="dk-url mono"><?= $e(Format::linkText($url)) ?>/</span></p>
          <div class="dk-actions">
            <button type="button" data-copy="<?= $e($url) ?>" data-copy-status="share-status" data-copy-fail="Could not copy. Select the link and copy it." hidden>Copy link</button>
            <button type="button" data-share="<?= $e($url) ?>" data-share-title="<?= $e($shareTitle) ?>" hidden>Share</button>
          </div>
          <span class="sr-only" id="share-status" role="status"></span>
        </div>
        <div class="dk-badge">
          <span class="cap">Badge for your README</span>
          <div class="dk-badge-head">
            <p>Listed here? Add this badge to your README.</p>
            <img src="/badge.svg" width="184" height="20" alt="Featured on phplives.dev badge">
          </div>
          <div class="cli-line"><code><?= $e($badge) ?></code><button type="button" data-copy="<?= $e($badge) ?>" data-copy-status="badge-status" data-copy-fail="Could not copy. Select the Markdown and copy it." hidden>Copy</button></div>
          <span class="sr-only" id="badge-status" role="status"></span>
        </div>
        <div class="dk-qr">
          <span class="cap">QR code for this page</span>
          <div class="dk-qr-body">
            <div>
              <p>Put it on a slide, a poster or a flyer. A phone camera opens this page.</p>
              <div class="dk-qr-actions">
<?php foreach (array_filter(['svg' => QrCode::svgPath($entry), 'png' => $qrPng ? QrCode::pngPath($entry) : null]) as $type => $path): ?>
                <a href="<?= $e($path) ?>" download="<?= $e(QrCode::fileName($entry, $type)) ?>">Download <?= strtoupper($type) ?><span class="sr-only"> of the QR code</span></a>
<?php endforeach ?>
              </div>
            </div>
            <svg class="qr-code" width="<?= $qr->size() * 3 ?>" height="<?= $qr->size() * 3 ?>" viewBox="0 0 <?= $qr->size() * 3 ?> <?= $qr->size() * 3 ?>" shape-rendering="crispEdges" aria-hidden="true"><path d="<?= $qr->path(3) ?>"/></svg>
          </div>
        </div>
      </section>
<?php if ($before !== null || $after !== null): ?>
      <nav class="nbr" aria-label="Other parcels">
<?php foreach (['prev' => $before, 'next' => $after] as $rel => $other): if ($other === null) continue; $tid = $site->trackingNumber($other); $y = 0; ?>
        <a class="nstub nstub-<?= $rel ?>" rel="<?= $rel ?>" href="<?= $e($site::entryPath($other)) ?>">
          <svg class="nstub-bars" viewBox="0 0 30 <?= array_sum(array_column(Format::barcode($tid), 'width')) * 1.6 ?>" preserveAspectRatio="none" aria-hidden="true">
<?php foreach (Format::barcode($tid) as $bar): $h = $bar['width'] * 1.6; ?>
<?php if ($bar['ink']): ?>            <rect y="<?= $y ?>" width="30" height="<?= $h ?>"/>
<?php endif; $y += $h; endforeach ?>
          </svg>
          <span class="nstub-body">
            <span class="nstub-cap"><?php if ($rel === 'prev'): ?><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 18-6-6 6-6"/></svg><?php endif ?><?= $rel === 'prev' ? 'Shipped before' : 'Shipped after' ?><?php if ($rel === 'next'): ?><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg><?php endif ?></span>
            <span class="nstub-name"><?= $e($other->name) ?></span>
            <span class="nstub-meta"><span class="nstub-date mono"><?= $e(Format::date($other->shipped)) ?></span><span class="nstub-code" aria-label="Stack: <?= $e(Format::stackLabel($other->stack)) ?>"><?= $e(Format::stackCode($other->stack)) ?></span></span>
            <span class="nstub-tid mono"><?= $e($tid) ?></span>
          </span>
        </a>
<?php endforeach ?>
      </nav>
<?php endif ?>
    </div>
  </div>
  <script><?= $copyScript ?></script>
  <script><?= $shareScript ?></script>
