<?php
/**
 * The Map view: a depot, one hub for each stack (or each topic), and the branch of the
 * hub the visitor opens. The hubs and the bins are stack and topic choices, so the
 * filters of the page drive them, and assets/manifest.js counts them and moves the
 * branch under the open hub. Without the script the view stays hidden.
 *
 * @var callable $e
 * @var \PhpLives\Site $site
 * @var list<\PhpLives\Entry> $entries
 * @var array<string, int> $counts Entries per stack, most common first.
 * @var array<string, int> $topics Entries per topic, most common first.
 * @var int $noTopic Entries with no topic. They sit under Other.
 * @var string|null $newestSlug
 * @var string $today
 */
use PhpLives\Format;

$mapTopics = $topics + ($noTopic > 0 ? ['none' => $noTopic] : []);
$topicLabel = fn (string $topic): string => $topic === 'none' ? 'Other' : Format::topicLabel($topic);
$chevron = '<svg class="map-chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>';
?>
      <section class="map" data-view="map" aria-label="Manifest map" hidden>
        <div class="map-chart" data-map data-map-limit="6">
          <div class="map-depot">
            <div class="map-depot-head"><b>Depot</b><span class="mono"><?= $e($today) ?></span></div>
            <p class="map-depot-row"><span>Parcels</span> <b class="mono" data-map-depot><?= count($entries) ?></b></p>
            <p class="map-depot-row map-only" data-map-only hidden><span data-map-only-kind>Topic</span> <b data-map-only-label></b> <button type="button" class="map-only-clear" data-map-only-clear>Clear<span class="sr-only"> this filter</span></button></p>
            <p class="map-depot-foot">Pick one to open its branch.</p>
          </div>
          <ul class="map-hubs" data-map-hubs="stack" aria-label="Stacks">
<?php foreach ($counts as $stack => $count): ?>
            <li class="map-node" data-map-node>
              <button type="button" class="map-hub" data-map-hub data-stack-choice="<?= $e($stack) ?>" data-stack-label="<?= $e(Format::stackLabel($stack)) ?>" aria-pressed="false" aria-expanded="false"><b class="map-code" aria-hidden="true"><?= $e(Format::stackCode($stack)) ?></b><span class="map-name"><?= $e(Format::stackLabel($stack)) ?></span><span class="map-n mono" data-map-count><?= $count ?></span><?= $chevron ?></button>
            </li>
<?php endforeach ?>
          </ul>
          <ul class="map-hubs map-hubs-topic" data-map-hubs="topic" aria-label="Topics" hidden>
<?php foreach ($mapTopics as $topic => $count): ?>
            <li class="map-node" data-map-node>
              <button type="button" class="map-hub" data-map-hub data-topic-choice="<?= $e($topic) ?>" data-topic-label="<?= $e($topicLabel($topic)) ?>" aria-pressed="false" aria-expanded="false"><span class="map-name"><?= $e($topicLabel($topic)) ?></span><span class="map-n mono" data-map-count><?= $count ?></span><?= $chevron ?></button>
            </li>
<?php endforeach ?>
          </ul>
          <div class="map-branch" data-map-branch hidden>
            <div class="map-bins" data-map-bins="stack" role="group" aria-label="Narrow by topic">
              <button type="button" class="map-bin" data-map-bin="stack" data-topic-choice="" data-topic-label="" aria-pressed="true"><span class="map-name">All</span><span class="map-n mono" data-map-count></span></button>
<?php foreach ($mapTopics as $topic => $count): ?>
              <button type="button" class="map-bin" data-map-bin="stack" data-topic-choice="<?= $e($topic) ?>" data-topic-label="<?= $e($topicLabel($topic)) ?>" aria-pressed="false"><span class="map-name"><?= $e($topicLabel($topic)) ?></span><span class="map-n mono" data-map-count></span></button>
<?php endforeach ?>
            </div>
            <div class="map-bins" data-map-bins="topic" role="group" aria-label="Narrow by stack" hidden>
              <button type="button" class="map-bin" data-map-bin="topic" data-stack-choice="" data-stack-label="" aria-pressed="true"><span class="map-name">All</span><span class="map-n mono" data-map-count></span></button>
<?php foreach ($counts as $stack => $count): ?>
              <button type="button" class="map-bin" data-map-bin="topic" data-stack-choice="<?= $e($stack) ?>" data-stack-label="<?= $e(Format::stackLabel($stack)) ?>" aria-pressed="false"><b class="map-code" aria-hidden="true"><?= $e(Format::stackCode($stack)) ?></b><span class="map-name"><?= $e(Format::stackLabel($stack)) ?></span><span class="map-n mono" data-map-count></span></button>
<?php endforeach ?>
            </div>
            <div class="map-shelf">
              <div class="bundle-head">
                <h3 data-map-title></h3>
                <span class="bundle-count mono" data-map-total></span>
              </div>
              <div class="stubs map-stubs">
<?php $idPrefix = 'm-'; $mapStub = true; foreach ($entries as $j => $entry) {
    require __DIR__.'/stub.php';
} $idPrefix = 's-'; $mapStub = false; ?>
              </div>
              <button type="button" class="map-more" data-map-more hidden>Show all</button>
            </div>
          </div>
        </div>
      </section>
