<?php
/**
 * A filter by stack or by topic: chips for a few choices, a dropdown for many.
 *
 * @var callable $e
 * @var array<string, int> $counts Entries per stack or topic, most common first.
 * @var int $maxChips
 * @var string $kind 'stack' or 'topic'.
 * @var int $total Entries on the page, for "All".
 */
use PhpLives\Format;

$caption = $kind === 'topic' ? 'Topic' : 'Stack';
$label = fn (string $key): string => $kind === 'topic' ? Format::topicLabel($key) : Format::stackLabel($key);
?>
<?php if (count($counts) <= $maxChips): ?>
          <div class="filter filter-<?= $e($kind) ?>" role="group" aria-label="Filter by <?= $e($kind) ?>">
            <span class="filter-cap" aria-hidden="true"><?= $e($caption) ?></span>
            <button type="button" data-<?= $e($kind) ?>-choice="" data-<?= $e($kind) ?>-label="" data-count="<?= $total ?>" aria-pressed="true">All <span class="n"><?= $total ?></span></button>
<?php foreach ($counts as $key => $count): ?>
<?php if ($kind === 'topic'): ?>
            <button type="button" data-topic-choice="<?= $e($key) ?>" data-topic-label="<?= $e($label($key)) ?>" data-count="<?= $count ?>" aria-pressed="false" aria-label="<?= $e($label($key)) ?>, <?= $count ?> <?= $count === 1 ? 'entry' : 'entries' ?>"><?= $e($label($key)) ?> <span class="n"><?= $count ?></span></button>
<?php else: ?>
            <button type="button" data-stack-choice="<?= $e($key) ?>" data-stack-label="<?= $e($label($key)) ?>" data-count="<?= $count ?>" aria-pressed="false" aria-label="<?= $e(Format::stackCode($key)) ?>, <?= $e($label($key)) ?>, <?= $count ?> <?= $count === 1 ? 'entry' : 'entries' ?>"><b><?= $e(Format::stackCode($key)) ?></b> <span class="n"><?= $count ?></span></button>
<?php endif ?>
<?php endforeach ?>
          </div>
<?php else: ?>
          <div class="filter filter-select filter-<?= $e($kind) ?>">
            <label class="filter-cap" for="<?= $e($kind) ?>-filter"><?= $e($caption) ?></label>
            <select id="<?= $e($kind) ?>-filter" data-<?= $e($kind) ?>-select>
              <option value="" data-<?= $e($kind) ?>-label="" data-count="<?= $total ?>">All (<?= $total ?>)</option>
<?php foreach ($counts as $key => $count): ?>
              <option value="<?= $e($key) ?>" data-<?= $e($kind) ?>-label="<?= $e($label($key)) ?>" data-count="<?= $count ?>"><?= $e($label($key)) ?> (<?= $count ?>)</option>
<?php endforeach ?>
            </select>
          </div>
<?php endif ?>
