<?php
/**
 * One stub: the top of a label, torn off along its perforation. It holds the route
 * code, the name, the sentence and one credits row, and links to the entry's page.
 *
 * @var callable $e
 * @var \PhpLives\Site $site
 * @var \PhpLives\Entry $entry
 * @var int $j Its place in its bundle. It times the tear-in.
 * @var string|null $newestSlug The slug of the newest parcel, which gets the NEWEST tag.
 */
use PhpLives\Format;

$id = 's-'.$e($entry->slug);
$link = $site->entryLink($entry);
$isNewest = $entry->slug === ($newestSlug ?? null);
?>
            <article class="stub<?= $isNewest ? ' newest' : '' ?>" style="--j: <?= min($j, 11) ?>" data-stack="<?= $e($entry->stack) ?>" aria-labelledby="<?= $id ?>">
              <div class="stub-paper">
                <div class="stub-top">
                  <h4 id="<?= $id ?>"><?= $e($entry->name) ?></h4>
                  <div class="route" aria-label="Stack: <?= $e(Format::stackLabel($entry->stack)) ?>"><b aria-hidden="true"><?= $e(Format::stackCode($entry->stack)) ?></b></div>
                </div>
<?php if ($isNewest): ?>
                <span class="new-tag">Newest</span>
<?php endif ?>
                <p class="stub-sentence"><?= $e($entry->sentence) ?></p>
                <p class="stub-cred"><span class="stub-from"><?= $e(Format::credit($entry->makers)) ?></span> <span class="stub-date mono"><time datetime="<?= $entry->shipped->format('Y-m-d') ?>"><?= $e(Format::date($entry->shipped)) ?></time></span></p>
<?php if ($link !== null): ?>
                <a class="stub-more" href="<?= $e($link) ?>">Details<span class="sr-only"> about <?= $e($entry->name) ?></span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                </a>
<?php else: ?>
                <a class="stub-more" href="<?= $e($entry->source) ?>">Source<span class="sr-only"> of <?= $e($entry->name) ?></span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></svg>
                </a>
<?php endif ?>
              </div>
            </article>
