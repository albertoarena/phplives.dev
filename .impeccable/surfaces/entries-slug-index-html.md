---
version: 1
slug: "entries-slug-index-html"
primary_target: "entries/slug/index.html"
related_targets: []
---

## Scope

One page per entry at /entries/{slug}/. Mode: Experience. Main visitor: the maker,
who shares the page as proof of their own work; second, a developer who arrives
from that shared link. Job: show the parcel at full presence, give the verifiable
details, and lead on to the rest of the list.

## Direction contract

THESIS: The parcel sits large in the centre, with the parcels shipped just before
and after it peeking in at the edges, so one entry still proves the list is long.
It refuses the generic product page (headline, screenshot, feature list).

OWN-WORLD: the Shipping Manifest world from DESIGN.md, unchanged: the full shipping
label (FROM, CONTENTS, SHIPPED with postmark and waves, barcode, TRACK, tracking
number) enlarged, on the pale or night field. Neighbour parcels are their tear-off
stubs, cut by the viewport edge, quieter (no shadow lift, lower contrast is NOT
allowed: they stay paper, just partly off-screen). Details sit on a white docket
sheet under the label with print rules; the install line uses the command strip
with Copy.

STORY: the maker opens their page and sees their parcel stamped and large; a
visitor reads the details, follows the source, then steps to the next parcel or
back to the manifest.

FIRST VIEWPORT: top bar; a "back to the manifest" link; the full label centred at
about 560 to 640px wide; at the left and right edges, the previous and next parcels
as stubs cut off by the viewport, each a link ("Shipped before", "Shipped after").
Below 1100px the side stubs move under the docket as a two-cell row.

FORM: On the depot shelf, candidate 7 on the ordered list, seed key 52a561dc. The
docket holds: the optional `details` text (2 or 3 sentences, only from the
project's own source), `composer require` when the entry has a package, licence when
given, links (source, date proof, docs when given), shipped, added and checked
dates, and the /dev/null links of the makers. A share row: copy link. Each entry
page has its own title, description and canonical URL; the share image stays the
site card for now. Signature: the label prints in and stamps, as on the home page.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance

## Unresolved

- Per-entry share images (generated PNG per entry) are a later step.
- New optional entry fields (details, package, licence, docs) need CONTRIBUTING.md
  rules; the details text follows the same "only what the source says" rule.
