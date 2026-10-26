<?php
/**
 * The manifest table: one row per parcel, in groups. The archive and the main page's
 * List view share it. Each group is a tbody with a head row.
 *
 * @var callable $e
 * @var list<\PhpLives\Entry> $entries Every entry in the table, newest first. It sets the numbers.
 * @var list<\PhpLives\Bundle> $groups
 * @var string|null $newest The slug of the entry to tag as the newest parcel.
 * @var string $groupPrefix Added to each group's id, so the ids stay unique on the page.
 * @var \PhpLives\Site $site
 */
use PhpLives\Format;

$numbers = [];
foreach ($entries as $i => $entry) {
    $numbers[$entry->slug] = count($entries) - $i;
}
?>
      <div class="scroll">
        <table>
          <thead>
            <tr><th scope="col">No.</th><th scope="col">Shipped</th><th scope="col">Project</th><th scope="col">Contents</th><th scope="col">From</th><th scope="col">Stack</th><th scope="col">Track</th></tr>
          </thead>
<?php foreach ($groups as $group): $n = count($group->entries); ?>
          <tbody id="<?= $e($groupPrefix.$group->id) ?>" data-group>
            <tr class="grp"><th colspan="7" scope="colgroup"><?= $e($group->title) ?> <span class="mono" data-group-count><?= $n ?> <?= $n === 1 ? 'parcel' : 'parcels' ?></span></th></tr>
<?php foreach ($group->entries as $entry): ?>
            <tr data-stack="<?= $e($entry->stack) ?>" data-topics="<?= $e(implode(' ', $entry->topics)) ?>">
              <td class="no mono"><?= str_pad((string) $numbers[$entry->slug], 3, '0', STR_PAD_LEFT) ?></td>
              <td class="date mono"><time datetime="<?= $entry->shipped->format('Y-m-d') ?>"><?= $e(Format::date($entry->shipped)) ?></time></td>
              <td class="name"><?= $e($entry->name) ?><?php if ($entry->slug === $newest): ?> <span class="new-tag">Newest</span><?php endif ?></td>
              <td class="desc"><?= $e($entry->sentence) ?></td>
              <td class="from"><?= $e(Format::credit($entry->makers)) ?></td>
              <td class="stk"><span class="code"><?= $e(Format::stackCode($entry->stack)) ?></span><span class="code-l"><?= $e(Format::stackLabel($entry->stack)) ?></span></td>
              <td class="trk"><a class="mono" href="<?= $e($entry->source) ?>"><?= $e(Format::linkText($entry->source)) ?></a><?php if ($site->hasEntryPages()): ?><a class="more" href="<?= $e($site::entryPath($entry)) ?>">Details<span class="sr-only"> for <?= $e($entry->name) ?></span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></a><?php endif ?></td>
            </tr>
<?php endforeach ?>
          </tbody>
<?php endforeach ?>
        </table>
      </div>
