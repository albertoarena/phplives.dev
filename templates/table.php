<?php
/**
 * The manifest table: one row per parcel. The archive and the main page's List view share it.
 *
 * @var callable $e
 * @var list<\PhpLives\Entry> $entries
 * @var bool $markNewest Tag the first row as the newest parcel.
 * @var \PhpLives\Site $site
 */
use PhpLives\Format;
?>
      <div class="scroll">
        <table>
          <thead>
            <tr><th scope="col">No.</th><th scope="col">Shipped</th><th scope="col">Project</th><th scope="col">Contents</th><th scope="col">From</th><th scope="col">Stack</th><th scope="col">Track</th></tr>
          </thead>
          <tbody>
<?php foreach ($entries as $i => $entry): ?>
            <tr data-stack="<?= $e($entry->stack) ?>">
              <td class="no mono"><?= str_pad((string) (count($entries) - $i), 3, '0', STR_PAD_LEFT) ?></td>
              <td class="date mono"><time datetime="<?= $entry->shipped->format('Y-m-d') ?>"><?= $e(Format::date($entry->shipped)) ?></time></td>
              <td class="name"><?= $e($entry->name) ?><?php if ($markNewest && $i === 0): ?> <span class="new-tag">Newest</span><?php endif ?></td>
              <td class="desc"><?= $e($entry->sentence) ?></td>
              <td class="from"><?= $e(Format::credit($entry->makers)) ?></td>
              <td class="stk"><span class="code"><?= $e(Format::stackCode($entry->stack)) ?></span><span class="code-l"><?= $e(Format::stackLabel($entry->stack)) ?></span></td>
              <td class="trk"><a class="mono" href="<?= $e($entry->source) ?>"><?= $e(Format::linkText($entry->source)) ?></a><?php if ($site->hasEntryPages()): ?><a class="more" href="<?= $e($site::entryPath($entry)) ?>">Details<span class="sr-only"> for <?= $e($entry->name) ?></span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></a><?php endif ?></td>
            </tr>
<?php endforeach ?>
          </tbody>
        </table>
      </div>
