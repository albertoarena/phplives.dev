<?php
/**
 * The stack filter: chips for a few stacks, a dropdown for many.
 *
 * @var callable $e
 * @var array<string, int> $counts Entries per stack, most common first.
 * @var int $maxChips
 */
use PhpLives\Format;

$total = array_sum($counts);
?>
<?php if (count($counts) <= $maxChips): ?>
          <div class="filter" role="group" aria-label="Filter by stack">
            <span class="filter-cap" aria-hidden="true">Stack</span>
            <button type="button" data-stack-choice="" data-stack-label="" data-count="<?= $total ?>" aria-pressed="true">All <span class="n"><?= $total ?></span></button>
<?php foreach ($counts as $stack => $count): ?>
            <button type="button" data-stack-choice="<?= $e($stack) ?>" data-stack-label="<?= $e(Format::stackLabel($stack)) ?>" data-count="<?= $count ?>" aria-pressed="false" aria-label="<?= $e(Format::stackCode($stack)) ?>, <?= $e(Format::stackLabel($stack)) ?>, <?= $count ?> <?= $count === 1 ? 'entry' : 'entries' ?>"><b><?= $e(Format::stackCode($stack)) ?></b> <span class="n"><?= $count ?></span></button>
<?php endforeach ?>
          </div>
<?php else: ?>
          <div class="filter filter-select">
            <label class="filter-cap" for="stack-filter">Stack</label>
            <select id="stack-filter" data-stack-select>
              <option value="" data-stack-label="" data-count="<?= $total ?>">All (<?= $total ?>)</option>
<?php foreach ($counts as $stack => $count): ?>
              <option value="<?= $e($stack) ?>" data-stack-label="<?= $e(Format::stackLabel($stack)) ?>" data-count="<?= $count ?>"><?= $e(Format::stackLabel($stack)) ?> (<?= $count ?>)</option>
<?php endforeach ?>
            </select>
          </div>
<?php endif ?>
