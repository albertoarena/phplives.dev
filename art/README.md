# Art

The brand art of phplives.dev:

- `favicon.svg`: the site's icon.
- `share-card.png`: the image that shows when someone shares a link to the site
  (1200x630).
- `share-card.html`: the page the share card is drawn from. Open it in a browser
  at 1200x630 and save it as PNG.
- `badge.svg`: the README badge (184x20). Listed projects can link it to their
  entry page. Its text is drawn as paths, because GitHub shows README images with
  no web fonts. The paths come from the card fonts in `assets/fonts/card/`
  (`archivo-100-700.ttf` for "FEATURED ON", `archivo-62-900.ttf` for
  "PHPLIVES.DEV"). They were made once with the Python library fontTools, in a
  separate virtual environment. fontTools is not a dependency of the repo. To
  change the text, draw the paths again in the same way.

The build copies the icon, the share card and the badge into the site. These files are not
covered by the MIT licence. [`TRADEMARK.md`](../TRADEMARK.md) says what you can do
with them.
