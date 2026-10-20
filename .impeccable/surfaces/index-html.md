---
version: 1
slug: "index-html"
primary_target: "index.html"
related_targets: []
---

## Scope

The main page of phplives.dev, plus the archive page. Mode: Experience, where the
entries lead from the first viewport. Visitor: a PHP developer who wants one link
that proves PHP is alive. Job: see in five seconds that the list is real and recent,
then share it. Redesign of 20/10/2026: the page of 36 identical full labels was too
crowded to read and would be useless at 120. Alberto chose: few full labels, grouping
by week and month, and the "week bundles of tear-off stubs" composition.

## Direction contract

THESIS: Only this week's parcels wear the full label; every older entry is its
tear-off stub, bundled by week, then by month. It refuses a wall of identical cards
and the awesome-list link dump.

OWN-WORLD: unchanged Shipping Manifest world (DESIGN.md): Prussian ink on the pale
field, thermal-white paper, 3px print rules, inverse route codes, one ring postmark
in Prussian ink, Signal Orange only for the newest parcel. The stub is new: the top
of a label torn off along a perforation (dashed Perforation edge), holding the route
code block, name, one sentence, mono date and maker, and a "Details" link to the
entry page. No barcode, postmark, waves or tracking number on a stub. Stubs sit on a
strict grid, 4 per row on desktop, with one credits row (maker, date) per stub
(raise from the design annual plate section: hairline-grid discipline).

STORY: the visitor reads the line, sees the slip, sees this week's parcels at full
size, then scans week and month bundles of stubs and opens one entry page or shares.

FIRST VIEWPORT: a shorter hero (line left, manifest slip right), then "This week"
with up to 3 full labels in one row, starting inside the fold. If fewer than 3
entries were added this week, the newest 3 by added date take the full label.

FORM: Week bundles of tear-off stubs, candidate 3 on the ordered list, seed key
bd899783. Groups: the current week's older stubs, then "Week NN, DD/MM to DD/MM" for
the previous weeks of the current month, then one group per month ("September
2026") with a mono count. The stack filter and Labels/List toggle stay; List shows
the manifest sheet grouped the same way. Archive keeps the dense sheet. Signature:
the full labels print in and stamp; stubs only tear in (short clip reveal), off under
prefers-reduced-motion.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance

## Unresolved

- Grouping key: by added date or shipped date. Recommend shipped date for the
  groups (the site's story is when things shipped) and added date only for "This
  week"; confirm with Alberto.
- The Dispatch log strip stays hidden until Alberto has a /dev/null post that links
  to phplives.dev.
