<?php
/**
 * The feeds page: the main feed, one feed for each stack that has entries, and one
 * for each topic that has a feed.
 *
 * @var callable $e
 * @var array<string, int> $counts Entries per stack, most common first.
 * @var array<string, int> $topics Entries per topic with a feed, most common first.
 * @var string $copyScript
 */
use PhpLives\Feed;
use PhpLives\Format;
use PhpLives\Site;

$groups = [
    '' => [['name' => 'Every entry', 'code' => null, 'path' => '/feed.xml', 'use' => 'The '.Feed::MAX_ENTRIES.' newest entries, in the order they were added to the site.']],
    'By stack' => [],
    'By topic' => [],
];

foreach (array_keys($counts) as $stack) {
    $groups['By stack'][] = ['name' => Format::stackLabel($stack), 'code' => Format::stackCode($stack), 'path' => "/feed/{$stack}.xml", 'use' => 'Only the '.Format::stackLabel($stack).' entries.'];
}

foreach (array_keys($topics) as $topic) {
    $groups['By topic'][] = ['name' => Format::topicLabel($topic), 'code' => null, 'path' => "/feed/topic/{$topic}.xml", 'use' => 'Only the entries with the topic '.Format::topicLabel($topic).'.'];
}

$groups = array_filter($groups);
$i = 0;
?>
  <div class="wrap">
    <section class="head">
      <div>
        <h1>Feeds</h1>
        <p>Follow new entries in your feed reader. Copy a link below and paste it into your reader.</p>
      </div>
    </section>
    <section class="sheet feeds" aria-labelledby="feeds-title">
      <div class="sheet-head">
        <h2 id="feeds-title">Subscribe</h2>
        <span class="mono"><?= array_sum(array_map(count(...), $groups)) ?> feeds</span>
      </div>
<?php foreach ($groups as $title => $feeds): ?>
<?php if ($title !== ''): ?>
      <h3 class="fd-group"><?= $e($title) ?></h3>
<?php endif ?>
      <ul class="fd-list">
<?php foreach ($feeds as $feed): $url = Site::URL.$feed['path']; $i++; ?>
        <li>
          <p class="fd-name"><?php if ($feed['code'] !== null): ?><span class="code" aria-hidden="true"><?= $e($feed['code']) ?></span> <?php endif ?><a href="<?= $e($url) ?>"><?= $e($feed['name']) ?></a></p>
          <p class="fd-use"><?= $e($feed['use']) ?></p>
          <div class="cli-line"><code><?= $e(Format::linkText($url)) ?></code><button type="button" data-copy="<?= $e($url) ?>" data-copy-status="feed-status-<?= $i ?>" aria-label="Copy the link of the feed: <?= $e($feed['name']) ?>" hidden>Copy</button></div>
          <span class="sr-only" id="feed-status-<?= $i ?>" role="status"></span>
        </li>
<?php endforeach ?>
      </ul>
<?php endforeach ?>
      <div class="sheet-foot"><span>A new entry shows in the feeds on the day it is added. The feeds use the Atom format. Every feed reader can read it.</span></div>
    </section>
  </div>
  <script><?= $copyScript ?></script>
