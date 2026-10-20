---
name: phplives.dev
description: A manifest of real things people shipped with PHP in the last 6 months.
colors:
  prussian-ink: "#12285a"
  signal-orange: "#ff5a1f"
  pale-field: "#e6eaf2"
  field-muted-ink: "#3a4870"
  field-line: "rgba(18,40,90,.18)"
  thermal-white: "#ffffff"
  print-black: "#121212"
  print-grey: "#4a4a46"
  soft-rule: "#d9d9d2"
  perforation: "#b9b9b2"
  band-text: "#f4f7f2"
  band-muted-ink: "#bccbe8"
  night-field: "#0d1d42"
  night-band: "#081330"
  night-label: "#e4e4dc"
  night-text: "#eef1f8"
  night-line: "rgba(238,241,248,.16)"
  night-print-grey: "#42423d"
  night-soft-rule: "#c9c9c0"
  night-perforation: "#a9a9a0"
typography:
  display:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "clamp(52px, 6cqi, 84px)"
    fontWeight: 850
    lineHeight: 0.94
    letterSpacing: "-0.02em"
    fontVariation: "'wdth' 70"
  headline:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "clamp(48px, 5.6cqi, 80px)"
    fontWeight: 850
    lineHeight: 0.95
    letterSpacing: "-0.02em"
    fontVariation: "'wdth' 70"
  title:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "34px"
    fontWeight: 850
    lineHeight: 1
    letterSpacing: "-0.015em"
    fontVariation: "'wdth' 72"
  section:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "30px"
    fontWeight: 800
    letterSpacing: "-0.01em"
    fontVariation: "'wdth' 75"
  bundle:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "22px"
    fontWeight: 800
    lineHeight: 1.1
    letterSpacing: "-0.005em"
    fontVariation: "'wdth' 75"
  body:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "17px"
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "11px"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "0.09em"
  route-code:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "44px"
    fontWeight: 900
    lineHeight: 0.86
    letterSpacing: "0.01em"
    fontVariation: "'wdth' 62"
  mono-date:
    fontFamily: "JetBrains Mono, ui-monospace, monospace"
    fontSize: "27px"
    fontWeight: 600
    letterSpacing: "-0.01em"
    fontFeature: "'tnum'"
  mono-data:
    fontFamily: "JetBrains Mono, ui-monospace, monospace"
    fontSize: "13px"
    fontWeight: 600
    fontFeature: "'tnum'"
rounded:
  sheet: "6px"
  sticker: "5px"
  strip: "4px"
  route: "3px"
  tag: "2px"
spacing:
  gutter: "40px"
  gutter-narrow: "16px"
  block: "16px 18px"
  grid-gap: "32px"
  grid-gap-narrow: "22px"
  stub-gap: "24px 20px"
  stub-gap-narrow: "18px 14px"
  stub-pad: "14px 16px 16px"
  bundle-gap: "52px"
  bundle-gap-narrow: "40px"
  hero-gap: "64px"
  band-gap: "72px"
  section-end: "96px"
components:
  button-cta:
    backgroundColor: "{colors.thermal-white}"
    textColor: "{colors.print-black}"
    rounded: "{rounded.sticker}"
    padding: "0 20px"
    height: "48px"
  toggle-option:
    textColor: "{colors.prussian-ink}"
    rounded: "{rounded.strip}"
    padding: "0 14px"
    height: "36px"
  toggle-option-pressed:
    backgroundColor: "{colors.prussian-ink}"
    textColor: "{colors.pale-field}"
    rounded: "{rounded.strip}"
    padding: "0 14px"
    height: "36px"
  shipping-label:
    backgroundColor: "{colors.thermal-white}"
    textColor: "{colors.print-black}"
    rounded: "{rounded.sheet}"
  label-block:
    padding: "{spacing.block}"
  stub:
    backgroundColor: "{colors.thermal-white}"
    textColor: "{colors.print-black}"
    rounded: "{rounded.sheet}"
    padding: "{spacing.stub-pad}"
  stub-route:
    backgroundColor: "{colors.print-black}"
    textColor: "{colors.thermal-white}"
    rounded: "{rounded.route}"
    padding: "5px 7px 4px"
  bundle-head:
    textColor: "{colors.prussian-ink}"
    typography: "{typography.bundle}"
    padding: "0 0 10px"
  route-code:
    backgroundColor: "{colors.print-black}"
    textColor: "{colors.thermal-white}"
    typography: "{typography.route-code}"
    rounded: "{rounded.route}"
    padding: "8px 10px 7px"
  newest-strip:
    backgroundColor: "{colors.signal-orange}"
    textColor: "{colors.print-black}"
    padding: "9px 18px"
  newest-tag:
    backgroundColor: "{colors.signal-orange}"
    textColor: "{colors.print-black}"
    rounded: "{rounded.tag}"
    padding: "3px 7px"
  manifest-sheet:
    backgroundColor: "{colors.thermal-white}"
    textColor: "{colors.print-black}"
    rounded: "{rounded.sheet}"
  devnull-band:
    backgroundColor: "{colors.prussian-ink}"
    textColor: "{colors.band-text}"
    padding: "88px 0"
  command-strip:
    backgroundColor: "{colors.thermal-white}"
    textColor: "{colors.print-black}"
    typography: "{typography.mono-data}"
    rounded: "{rounded.sticker}"
    padding: "6px 6px 6px 14px"
  copy-button:
    textColor: "{colors.print-black}"
    rounded: "{rounded.strip}"
    padding: "0 14px"
    height: "36px"
  entry-label:
    backgroundColor: "{colors.thermal-white}"
    textColor: "{colors.print-black}"
    rounded: "{rounded.sheet}"
    padding: "20px 24px"
    width: "600px"
  docket:
    backgroundColor: "{colors.thermal-white}"
    textColor: "{colors.print-black}"
    rounded: "{rounded.sheet}"
    width: "600px"
  docket-command-strip:
    backgroundColor: "{colors.soft-rule}"
    textColor: "{colors.print-black}"
    typography: "{typography.mono-data}"
    rounded: "{rounded.sticker}"
  neighbour-stub:
    backgroundColor: "{colors.thermal-white}"
    textColor: "{colors.print-black}"
    rounded: "{rounded.sheet}"
    padding: "16px 26px 14px 16px"
  stub-code:
    backgroundColor: "{colors.print-black}"
    textColor: "{colors.thermal-white}"
    rounded: "{rounded.tag}"
    padding: "4px 6px 3px"
  details-link:
    textColor: "{colors.print-black}"
    height: "24px"
---

# Design System: phplives.dev

## Overview

**Creative North Star: "The Shipping Manifest"**

Every project on the site is a parcel that shipped. Its postmark is the proof of the date. The page is a depot: a blue field, a manifest slip, this week's thermal shipping labels, and bundles of label stubs. The labels and stubs are printed in black ink. The visitor reads the line and checks the slip. Then the visitor scans the labels and the bundles: who, what, when, and where to check.

The world is printed, not drawn. Labels are white paper with heavy black print rules, a large inverse routing code per stack, a barcode and a ring postmark. A stub is the top of a label, torn off along its perforation. It keeps the name, the sentence, the maker and the date. Type is one condensed family, Archivo, with a monospace face for dates and tracking data. There are two colours: one Prussian blue ink and one signal orange. The orange marks only the newest parcel.

The design keeps out of the way of the entries. There are no project logos, no screenshots and no art. The one mark is the GitHub mark on the links to the site's own code. The system rejects the showcase grid of logo cards and the awesome-list link dump.

**Key Characteristics:**
- Paper on a blue field: all content sits on paper labels, stubs, sheets and strips.
- Full labels for the entries added last. Stubs for every other entry, in bundles by week and by month.
- Heavy print rules (3px black) split every label into its fields.
- Condensed Archivo display with wide word spacing (.12em).
- JetBrains Mono only for dates and tracking data.
- One signal colour, for one parcel.
- Light and dark themes, with the same Prussian ink in both.
- A print-in, a stamp and a tear-in on load, and nothing else moves.

## Colors

One deep Prussian blue ink, white paper, near-black print and a single signal orange.

### Primary
- **Prussian Ink** (`prussian-ink`): the ink of the whole world. In the light theme it is the type on the field and the background of the /dev/null band. On every label it is the colour of the ring postmark and the cancellation waves, in both themes. It is also the fill of the pressed state in toggles and chips.

### Secondary
- **Signal Orange** (`signal-orange`): marks the newest parcel and nothing else. It fills the "Newest parcel" strip at the top of the first label. It colours that label's postmark (ring, lettering and date). It fills the NEWEST tag in the List view. Text on it is Print Black.

### Neutral
- **Pale Field** (`pale-field`): the light theme background. A pale blue-grey, so the white labels stand out from it.
- **Field Muted Ink** (`field-muted-ink`): secondary text on the field, such as the lede and section notes.
- **Field Line** (`field-line`): hairlines on the field (top bar, footer), toggle group borders, and hover fills.
- **Thermal White** (`thermal-white`): the paper of labels, stubs, the manifest slip, the sheets, the dispatch strip, the command strip and the CTA button.
- **Print Black** (`print-black`): the ink on paper. It draws the 3px print rules, the route code blocks and the barcode.
- **Print Grey** (`print-grey`): captions and secondary text on paper (FROM, SHIPPED, date proof, tracking number).
- **Soft Rule** (`soft-rule`): 1px row dividers inside sheets and the slip. On a stub it is the line above the credits row.
- **Perforation** (`perforation`): dashed tear lines (the slip foot, the dispatch posts and the line under a stub's credits row).
- **Band Text** (`band-text`) and **Band Muted Ink** (`band-muted-ink`): text on the /dev/null band.

### Dark theme
The dark theme swaps the field and dims the paper. The ink and the signal stay the same.
- **Night Field** (`night-field`): the dark background, a deep Prussian.
- **Night Band** (`night-band`): the /dev/null band, a darker Prussian, so it still reads as a separate band.
- **Night Label** (`night-label`): the paper, dimmed so it does not glare at night.
- **Night Text** (`night-text`) and **Night Line** (`night-line`): type and hairlines on the night field. Muted text uses Band Muted Ink.
- **Night Print Grey**, **Night Soft Rule** and **Night Perforation**: the paper greys, tuned for the dimmer paper.

The theme follows the device by default. A Light / Dark / System toggle in the top bar sets `data-theme` on the root element and overrides it.

### Named Rules
**The Signal Rule.** Signal Orange marks only the newest parcel: its top strip, its postmark and the NEWEST tag in the List view. The cancellation waves on that label stay blue. The archive has no orange at all. A stub has no orange either. No button, link, hover or focus state is ever orange.

**The One Ink Rule.** The postmark and the waves use Prussian Ink on every label, in both themes. A second blue, or a coloured stack code, breaks the print.

## Typography

**Display Font:** Archivo (with system-ui, sans-serif), a variable font with width 62 to 125 and weight 100 to 900.
**Body Font:** Archivo.
**Label/Mono Font:** JetBrains Mono (with ui-monospace, monospace), weights 400 and 600, with tabular numbers.

**Character:** One grotesque family, set condensed and heavy for anything that is a headline, and at normal width for reading. The monospace face is the printer's data: dates, counts, links on the TRACK line, tracking numbers and the terminal command.

### Hierarchy
- **Display** (`display`): the line `PHP delivers. PHP lives!` only. "PHP lives!" sits on a white sticker, turned -1.2deg. The line drops to 54px on narrow screens.
- **Entry name** (850, clamp(40px, 9cqi, 56px), line height .95, width 72%): the project name as the h1 on its own entry page, inside the CONTENTS block. It is the title shape, made bigger. It is not the display line.
- **Headline** (`headline`): page heads on the archive, Privacy and Credits pages. The /dev/null band heading uses the same shape at clamp(40px, 4.6cqi, 64px).
- **Title** (`title`): the project name in the CONTENTS block of a label. Sheet and slip heads use the same width at 22px to 26px. The name on a home stub uses it at 22px. The name on a neighbour stub uses it at 24px (20px on narrow screens, clamp(22px, 2cqi, 28px) from 1100px).
- **Section** (`section`): section heads on the field, such as "Shipped since DD/MM/YYYY". They drop to 26px on narrow screens.
- **Bundle** (`bundle`): bundle heads on the field, such as "This week", "Week 43, 19/10 to 25/10" or "September 2026". A month head is 26px. Group head rows in the manifest sheet use the title width (72%) at 19px.
- **Body** (`body`): reading text. The lede is 19px. Read-mode pages (Privacy) use 17px with line height 1.6 and a 65ch measure. On the entry page, the sentence on the label is 19px (17px on narrow screens) and the docket details text is 17px. Docket values are 15px weight 600, maker names 16px weight 700, docket terms 13px weight 600 in Print Grey. The back link is 15px weight 700.
- **Label** (`label`): uppercase field captions on paper (FROM, CONTENTS, SHIPPED, TRACK) and table heads. The neighbour stub caption (Shipped before, Shipped after) uses it too.
- **Route code** (`route-code`): the three-letter stack code (LRV, SYM, NTV, WRD, PHP) in its inverse block. Filter chips and table cells use the same width at 17px to 18px. The chip on a home stub uses it at 22px. On the entry page the code grows to 56px (46px on narrow screens). On a neighbour stub it is 16px.
- **Mono date** (`mono-date`): the shipped date on a label. It is 34px on the entry page (28px on narrow screens).
- **Mono data** (`mono-data`): tracking numbers, link text on the TRACK line, counts, the slip values, the home stub date and the terminal command. Sizes run from 11px to 15px. On the entry page the neighbour stub date and the docket links are 14px, and the label's tracking number is 12px.
- **Details link** (Archivo 700, 13px on a label, 12px in a table row): the only small link that is not mono, because it is a word, not data.

### Named Rules
**The Wide Words Rule.** Every condensed heading (display, headline, title, section, bundle) gets word spacing of .12em. Condensed Archivo runs words together without it.

**The Printer's Data Rule.** JetBrains Mono is for dates and machine data only: dates, counts, tracking numbers, link text on TRACK and the command line. Names, sentences and buttons are Archivo.

**The Date Rule.** Every date on screen is DD/MM/YYYY. The postmark splits it into DD/MM over YYYY.

## Layout

The page is one column of sections inside a 1320px wrap with a 40px side gutter. The root is a size container, so the layout responds to its own width (container queries), not to the viewport.

- **Hero:** two columns (1.4fr and 1fr, 64px gap), aligned to the bottom. It is short: 32px of padding on top and 36px below (28px and 32px on narrow screens). The line is on the left, with the lede, the contribute row and its note under it. The white manifest slip is on the right. Below 1100px it becomes one column and the slip caps at 560px.
- **Section head:** the title on the left, with the note "Newest first." on the same line. The tools (stack filter, then Labels / List) are on the right. Below 700px they stack.
- **This week:** up to three full labels in one row, with a 32px gap. The first row of labels starts inside the first screen. At 960px and below, the row scrolls sideways with scroll snap. Each column is min(380px, 42%), so the next label shows at the edge. At 700px and below a column is 86% wide. The row runs into the side gutter, so labels scroll to the edge of the page.
- **Bundles:** the stubs sit in a grid of 4 columns, with gaps of 24px (rows) and 20px (columns). The grid has 3 columns at 1100px and below. It has 2 columns at 700px and below, with gaps of 18px and 14px. It has 1 column at 440px and below. All these widths are container widths.
- **Weeks share rows:** the week bundles that follow each other sit in one grid with the same columns. Each week spans one column per stub, up to the number of columns. Its stubs use a column subgrid, so they line up with the stubs of the other weeks. The stubs in one row have the same height.
- **Bundle rhythm:** "This week" ends with 64px of space (48px on narrow screens). Bundles are 52px apart (40px on narrow screens). After the last week, the first month bundle starts 72px below (52px on narrow screens).
- **/dev/null band:** full width, 88px top and bottom. Two columns (72px gap) when there are posts. With no posts it is one row: the heading on the left, the CTA and command strip on the right, with less padding (64px).
- **Sheets:** the archive, the List view, Privacy and Credits are white sheets inside the wrap. The Privacy sheet caps at 65ch plus padding.
- **Narrow screens:** below 700px the gutter drops to 16px and the "Commit it on /dev/null" nav link hides. Below 860px each table row becomes a small grid: date and stack code on top, then name, sentence, maker and link.
- **Rhythm:** sections end with 96px of space (64px on narrow screens). Label blocks have 16px by 18px of padding. A stub has 14px of padding on top, 16px on the sides and 16px at the bottom.

### Entry page (`/entries/{slug}/`)
One page per entry. Top to bottom:
- **Back link:** inside the wrap, with a left arrow. It reads "Back to the manifest", or "Back to the archive" for an archived entry. It is 44px tall.
- **Depot:** a full-bleed area outside the wrap, so it runs from page edge to page edge. It clips anything that goes past the sides. It has 16px of space on top and 96px at the end (8px and 64px on narrow screens).
- **Shelf:** a 600px column, centred in the depot. On narrow screens it is the full width minus the gutters (100% minus 80px, then minus 32px below 700px). It holds the entry label, then the docket 28px under it.
- **Neighbour stubs, from 1100px:** absolutely placed beside the label, 210px from the top of the depot. Each stub starts 40px past the page edge, so the edge always cuts its barcode and the stub reads as torn off. It is at most 360px wide, and it always leaves a gap of at least clamp(40px, 5cqi, 96px) before the label. On wide screens the gap grows, and the stub keeps its size.
- **Neighbour stubs, below 1100px:** a two-cell row under the docket, 28px below it, with a 16px gap (12px below 700px). The earlier parcel is always in the left cell and the later one in the right cell. The first and last entries show one stub, in its own cell.
- **Below 700px:** the label blocks drop to 16px by 18px of padding, the postmark to 124px, and the docket's side padding to 18px.

## Elevation & Depth

Depth is paper lying on a surface. Every sheet of paper (label, slip, sheet, sticker, CTA, command strip, docket, stub) has one soft drop shadow tinted with Prussian blue. Nothing else casts a shadow. A label lifts 3px on hover, and its shadow grows. On its own entry page the label does not lift, because it is not a link there. Stubs never lift: a home stub keeps its drop shadow, and a neighbour stub keeps the rest shadow. The command strip inside the docket has no shadow, because it is printed on the docket, not laid on it. In the dark theme the shadows are black and deeper.

### Shadow Vocabulary
- **Paper rest** (`--shadow`): every sheet of paper at rest. A 1px contact line plus a long soft fall.
- **Paper lifted** (`--shadow-hi`): a label on hover.
- **Paper drop** (`--shadow-drop`): a stub. It is a `filter: drop-shadow`, so the shadow follows the torn edge. Its fall is shorter than the fall of the paper rest shadow.

### Named Rules
**The Paper Only Rule.** Only paper casts a shadow. The field, the band, toggles and chips stay flat. Hard offset shadows are not part of this world.

## Shapes

Shapes are cut paper: small, tight corners and straight edges. Sheets and labels have a 6px radius. The sticker and the CTA have 5px. The dispatch strip and the small buttons have 4px. The route code has 3px and the NEWEST tag 2px. A stub has 6px corners at the top and a torn edge at the bottom. Nothing is a pill or a circle, except the postmark.

The paper carries print marks, not borders:
- **Print rule:** 3px solid Print Black. It splits label blocks and closes sheet heads and feet.
- **Table rule:** 2px Print Black under table heads and under each group head row.
- **Bundle rule:** 3px solid in the field's text colour, under each bundle head.
- **Soft rule:** 1px Soft Rule between rows.
- **Perforation:** dashed Perforation lines. The slip foot, the docket's share foot and the neighbour stub tear line use 2px dashes. Dispatch posts and the home stub foot use 1px dashes.
- **Torn edge:** the dispatch strip has a scalloped top edge, cut with a radial-gradient mask. A home stub has a scalloped bottom edge, cut the same way (4px circles, every 12px).

### Named Rules
**The Print Rule.** Blocks on paper are split by black print rules, never by gaps or cards inside cards. A label is one sheet with ruled fields.

## Components

### Buttons
Tactile and plain, like a sticker on a box.
- **Shape:** sticker corners (5px).
- **CTA:** "Commit it on /dev/null ↗" on the /dev/null band. White paper with Print Black text, weight 800, 48px tall, with the paper shadow and an inline arrow icon. It rises 1px on hover.
- **Contribute CTA:** the same paper button with the GitHub mark (Octicons, 20px) before the text: "Add your project" in the hero, "See the code on GitHub" in the footer. A bold underlined text link sits next to it for the second route ("or suggest one in an issue", "Read how to contribute").
- **Copy:** in the command strip, and "Copy link" in the docket's share foot. A 2px Print Black outline on paper, weight 800, 36px tall. It shows "Copied" or "Not copied" for 2 seconds, then returns to its own text. A hidden status line tells screen readers the result. It is hidden when the browser has no clipboard.
- **Focus:** a 2px outline in the colour of the text around it, with a 2px to 3px offset.

### Toggles and chips
Segmented groups on the field: the theme toggle (Light, Dark, System), the view toggle (Labels, List) and the stack filter.
- **Style:** a 1px Field Line border, 6px radius, 3px inner padding. Options are transparent, with weight 600 to 700. They are 32px tall in the theme toggle and 36px in the others.
- **State:** the pressed option fills with the field's text colour and takes the field colour as text. Hover fills with Field Line.
- **Stack chips:** "All" plus one chip per stack, up to 6, most common first. Each shows its route code in condensed weight 900 and a mono count. With more than 6 stacks a native select replaces the chips.
- **Behaviour:** the state lives in the URL hash (`#stack=laravel&view=list`). The view is also kept in `localStorage`. Without JavaScript the tools stay hidden. The page then shows the full labels and every bundle of stubs.
- **Filter and bundles:** the stack filter hides a bundle when none of its entries is left. It also rewrites the parcel count of each bundle.

### Shipping label (signature)
A 4x6 thermal label, one per entry. White paper, 6px radius, paper shadow. The label is its own size container. Its blocks, top to bottom:
1. **Newest parcel strip** (newest only): Signal Orange, uppercase weight 800.
2. **FROM:** one line per maker. The name links to their GitHub. A small mono "/dev/null ↗" link follows when they have a profile there. The route code block sits on the right.
3. **CONTENTS:** the project name (title) and one plain sentence.
4. **SHIPPED:** the mono date, the date proof link, the waves and the ring postmark. The postmark is 118px, turned -9deg, at 90% opacity, with a multiply blend. Its ring reads PHPLIVES.DEV over SHIPPED around the date. The waves fill only the gap between the date and the ring. They hide when the label is narrower than 378px.
5. **Barcode, TRACK and tracking number:** a full-width barcode, then TRACK with the source link and an arrow, then the tracking number in letter-spaced mono. On the home page a Details link sits on the same line as the tracking number, at the right.

The same label partial is used on the home page and on the entry page.

### Entry label
The shipping label, enlarged on its own entry page. It is 600px wide on the shelf. The blocks have 20px by 24px of padding. The project name is the page's h1, at clamp(40px, 9cqi, 56px). The route code is bigger (56px code, 92px wide block). The date is 34px. The postmark is 150px and the barcode 60px tall. The label does not lift on hover, and it has no Details link. The newest entry's page keeps the Signal Orange strip and the orange postmark.

### Docket
A white sheet under the entry label, with the paper shadow and a 6px radius. Its parts, top to bottom:
1. **Sheet head:** "Parcel details" and the tracking number in mono, closed by a print rule.
2. **Details text** (optional): two or three plain sentences, at 17px.
3. **Install** (only when the entry has a package): the command strip with `composer require` and the Copy button. Here the strip is a Soft Rule tint with no shadow.
4. **Facts:** a list of term and value, split by Soft Rule lines. The order is fixed: Made by, Source, Docs, Licence, Shipped, Date from, Added, Checked. Docs and Licence show only when the entry gives them. Terms sit in a 110px column (88px on narrow screens). Links carry a small out arrow. Made by lists each maker, with their GitHub and /dev/null links.
5. **Share foot:** a 2px perforation above it. "Share this parcel", the page address in mono, then the "Copy link" and "Share" buttons side by side, in the same outline style. "Copy link" is hidden when the browser has no clipboard. "Share" opens the device's own share menu, and it is hidden when the browser has none. The share card title is "Name: shipped with PHP", without the site name, because cards already show it.

### Neighbour stubs
The tear-off stubs of the parcels shipped just before and just after this one. They show that the list goes on. Each stub is a link.
- **Paper:** Thermal White (Night Label in the dark theme), 6px radius, the rest shadow. No lift, no rotation, no dimming.
- **Barcode:** a vertical barcode drawn from the neighbour's tracking number, on the outer side (left for before, right for after). It takes the full height of the stub. From 1100px it fills the paper from the page edge up to the text, so no blank paper shows between them. The text block is at most 210px wide.
- **Perforation:** a 2px dashed Perforation line on the side that faces the label.
- **Text:** the caption "Shipped before" with a left arrow, or "Shipped after" with a right arrow. Then the name in the title shape, the mono date, a small inverse stack code (2px radius) and the tracking number. The earlier stub is right-aligned, so its text sits next to the label. The name underlines on hover.
- **Placement:** see Layout. From 1100px they sit beside the label and the page edge cuts them. Below 1100px they sit as a row under the docket.

### Details link
A small bold link to the entry page, with a right arrow. On each home label it sits in the foot, beside the tracking number (13px). In the List view and the archive it sits under the source link in the Track column (12px). It is Archivo, not mono. Screen readers hear "Details for {name}".

### Stub (signature)
The top of a label, torn off along its perforation. There is one stub for each entry that does not get a full label. It is white paper with the paper drop shadow. The corners are 6px at the top, and the bottom edge is torn. Its parts, top to bottom:
1. **Name and route code:** the project name is on the left, at 22px and 72% width. The route code is a small inverse chip on the right, without the stack name. A 3px print rule sits under them.
2. **Sentence:** one plain sentence at 14px. It grows to fill the space, so the credits rows of the stubs in one row line up.
3. **Credits row:** a Soft Rule line is above it, and a dashed perforation line is below it. The makers are on the left, in bold. They wrap in their own column. The mono date stays on the right, on one line.
4. **Link:** while the site has no entry pages, "Source ↗" goes to the project's own source. When entry pages exist, "Details →" goes to `/entries/{slug}/`.

A stub has no barcode, postmark, waves or tracking number. It has no orange and no hover lift.

### Bundles
The main page groups the entries in bundles. Each bundle opens with a head on the field: a condensed heading on the left and a mono parcel count on the right, such as "4 parcels". A 3px rule in the field's text colour closes the head. Week heads are 22px and month heads are 26px.
- **This week:** up to 3 full labels. They are the entries added last, newest added first. The first one is the newest parcel, in Signal Orange. The heading is "This week". When nothing was added this week, the heading is "Added last". This bundle has a note in place of the count. The note says when the labels were added.
- **Weeks:** every other entry is a stub, grouped by the date it shipped. The current week comes first. Each earlier week of the current month follows, such as "Week 40, 01/10 to 04/10". When an earlier week starts in the month before, its title starts on the first of the month. Weeks are ISO weeks in UTC, from Monday to Sunday.
- **Months:** the older entries in the 6-month window are in one bundle per month, such as "September 2026".

### Route code
The stack code printed inverse: Print Black block, white condensed weight 900 code, the stack name in small type below it. The same block, smaller, marks the stack in table rows.

### Manifest slip
The white slip in the hero. A head with "Manifest" and today's date over a 3px print rule. Then rows of term and value: Parcels, Shipped since, Last shipped and Stacks. A perforated foot holds one note, and it hides on narrow screens.

### Manifest sheet
The table shared by the archive and the List view. A head and a foot closed by print rules. Columns: No., Shipped, Project, Contents, From, Stack, Track. Rows take a Field Line tint on hover. The first row of the List view carries the NEWEST tag.
- **Groups:** the rows are in groups, one table body per group. Each group opens with a head row: the title in condensed type at 19px, then a mono count. A 2px print rule closes the head row. The head row takes no hover tint.
- **List view:** the first group is "This week" (or "Added last"). The bundles of the main page follow it.
- **Archive:** one group per month.

### /dev/null band
A full-width Prussian band (Night Band in the dark theme). It holds the heading "Shipped something in PHP?", one line of text, the CTA and the command strip. The command strip is a white bar with the mono `curl` command and the Copy button. A note below it says what the command needs. The **Dispatch log** strip appears only when there are posts. It is a torn-edge paper strip with dated posts split by perforations.

### Read-mode sheets
Privacy and Credits use a page head on the field and one white sheet. Privacy is sections of body text at a 65ch measure. Credits is groups of items in three columns: the name, what the site uses it for, and its maker and licence. Inline code sits on a Soft Rule tint.

### Footer
On every page. It opens with the **contribute row**: a section-style heading ("Open source. Anyone can help."), one line of text, and the contribute CTA with its text link on the right. A Field Line hairline splits it from the rest: the maker line, the links, the fine print, and Privacy · Credits · Feed. Feed links to `/feed.xml`.

### Navigation
The top bar: the wordmark "phplives.dev" (condensed 75%, weight 800) on the left, then Manifest, Archive and "Commit it on /dev/null", then the theme toggle. The current page is underlined with a 2px line. Links on paper and on the field underline at 1px and thicken to 2px on hover.

### Motion
Labels print in once on load: each one reveals from top to bottom with a clip-path over .95s, 70ms after the one before. Then each postmark stamps down, scaling from 1.3 to 1 over .4s, 700ms after its label starts. The label on an entry page prints in and stamps in the same way. Home stubs do not print in or stamp. They only tear in. Each stub reveals from the top with a clip-path over .45s. At the same time it drops 6px and settles from -0.6deg. A stub starts 600ms after load, plus 40ms for each place in its bundle. The delay stops growing after the 12th stub. The docket and the neighbour stubs do not move. The print-in, the stamp and the tear-in are all off under `prefers-reduced-motion`. The theme change fades the field over .3s.

## Do's and Don'ts

### Do:
- **Do** put every piece of content on paper: a label, a stub, the slip, a sheet or a strip.
- **Do** split label fields with 3px Print Black rules.
- **Do** keep Signal Orange on the newest parcel only: strip, postmark and NEWEST tag.
- **Do** print the postmark and the waves in Prussian Ink, in both themes.
- **Do** set condensed headings in Archivo with .12em word spacing.
- **Do** use JetBrains Mono for dates and machine data, and write dates as DD/MM/YYYY.
- **Do** keep the page fully usable without JavaScript: full labels and bundles of stubs, tools hidden.
- **Do** give a home stub only the top of a label: route code, name, sentence, credits row and one link.
- **Do** cast a home stub's shadow with the drop-shadow filter (`--shadow-drop`), so it follows the torn edge.
- **Do** turn off the print-in, the stamp and the tear-in under `prefers-reduced-motion`.
- **Do** keep neighbour stubs full-contrast paper with the rest shadow, and let the page edge cut their barcode from 1100px.
- **Do** keep the earlier parcel on the left and the later parcel on the right, at every width.

### Don't:
- **Don't** build a showcase grid of logo cards or an awesome-list link dump.
- **Don't** use Signal Orange for buttons, links, hover, focus or any second highlight.
- **Don't** add a second accent colour or colour-code the stacks.
- **Don't** add shadows to anything that is not paper, or use hard offset shadows.
- **Don't** round corners past 6px or use pill shapes.
- **Don't** add motion beyond the print-in, the stamp, the stub tear-in, the label lift and the theme fade.
- **Don't** add a barcode, postmark, waves or tracking number to a stub.
- **Don't** show the cancellation waves on a label narrower than 378px.
- **Don't** dim, fade, lift or rotate a neighbour stub. It is paper, only partly on screen.
- **Don't** give the docket's command strip a shadow: it is printed on the docket.
