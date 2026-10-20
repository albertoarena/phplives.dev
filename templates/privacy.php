<?php
/**
 * The privacy page. Every claim about Cloudflare links to Cloudflare's own docs.
 *
 * @var callable $e
 */
$cf = [
    'about' => 'https://developers.cloudflare.com/web-analytics/about/',
    'beacon' => 'https://developers.cloudflare.com/speed/observatory/rum-beacon/',
    'collection' => 'https://developers.cloudflare.com/web-analytics/data-metrics/data-origin-and-collection/',
    'dimensions' => 'https://developers.cloudflare.com/web-analytics/data-metrics/dimensions/',
    'faq' => 'https://developers.cloudflare.com/web-analytics/faq/',
    'policy' => 'https://www.cloudflare.com/privacypolicy/',
];
$mail = 'hello@albertoarena.it';
?>
  <div class="wrap">
    <section class="head">
      <div>
        <h1>Privacy</h1>
        <p>What this site collects when you visit it, and what it does not.</p>
      </div>
    </section>
    <article class="sheet policy" aria-labelledby="in-short">
      <div class="policy-body">
        <section>
          <h2 id="in-short">In short</h2>
          <ul>
            <li>The site sets no cookies.</li>
            <li>It has no accounts and no forms.</li>
            <li>It counts visits with Cloudflare Web Analytics. Cloudflare says that this tool does not collect or use your personal data.</li>
            <li>The fonts come from this site, so no request goes to Google.</li>
          </ul>
        </section>

        <section>
          <h2>Data controller</h2>
          <p>Alberto Arena makes and runs phplives.dev. He is the data controller for this site.</p>
          <p>For any privacy request, write to <a href="mailto:<?= $e($mail) ?>"><?= $e($mail) ?></a>. His site is <a href="https://albertoarena.it/?utm_source=phplives.dev&amp;utm_medium=footer">albertoarena.it</a>.</p>
        </section>

        <section>
          <h2>Analytics</h2>
          <p>The site uses <a href="<?= $e($cf['about']) ?>">Cloudflare Web Analytics</a> to count visits. Each page loads a small script from Cloudflare. Cloudflare calls this script a beacon.</p>
          <p>The beacon sends this data to Cloudflare:</p>
          <ul>
            <li>performance data from your browser, such as how long the page took to load</li>
            <li>the address of the page</li>
            <li>the page that sent you here (the referrer)</li>
            <li>an ID for the page view, which the beacon makes in your browser</li>
          </ul>
          <p>Cloudflare says that the beacon does not store any data in your browser. Cloudflare also says that the beacon does not read cookies, <code>localStorage</code>, <code>sessionStorage</code> or IndexedDB.</p>
          <p>Cloudflare receives your IP address with the request from the beacon. This is part of normal request handling. Cloudflare says that it discards the IP address at the nearest data center. It does not store the address in its core databases or logs.</p>
          <p>For now, Web Analytics does not log query strings (the part of a link after <code>?</code>).</p>
          <p>The reports group visits by country, browser, operating system, device type, page and referring site. Cloudflare says that it does not track people across the websites of its customers.</p>
          <p>Cloudflare keeps the full beacon data for 7 days. After that, it cuts the data down to about 10%. The reports go back six months.</p>
          <p>Many ad blockers stop the beacon. The site works the same without it.</p>
          <p class="src">Sources, all from Cloudflare: <a href="<?= $e($cf['about']) ?>">About Web Analytics</a>, <a href="<?= $e($cf['beacon']) ?>">RUM beacon</a>, <a href="<?= $e($cf['collection']) ?>">Data origin and collection</a>, <a href="<?= $e($cf['dimensions']) ?>">Dimensions</a> and the <a href="<?= $e($cf['faq']) ?>">FAQ</a>.</p>
        </section>

        <section>
          <h2>Hosting</h2>
          <p>Cloudflare Workers serves the pages. To learn how Cloudflare handles the data of visitors, read the <a href="<?= $e($cf['policy']) ?>">Cloudflare privacy policy</a>.</p>
        </section>

        <section>
          <h2>Fonts</h2>
          <p>The pages use two fonts: Archivo and JetBrains Mono. This site serves them itself, from phplives.dev, like the pages.</p>
          <p>Your browser asks no other server for them. No third party sees these requests.</p>
        </section>

        <section>
          <h2>Legal basis</h2>
          <p>The site handles the analytics data and the page requests on the basis of a legitimate interest. This is GDPR Art. 6(1)(f).</p>
          <p>The interest is to serve the pages and to count visits. The site does this without cookies and without data that identifies you. That is why it asks for no consent.</p>
        </section>

        <section>
          <h2>What stays in your browser</h2>
          <p><strong>Your theme.</strong> When you pick Light or Dark, the site saves your choice in your browser. It uses <code>localStorage</code>, under the name <code>theme</code>. When you pick System, the site deletes the saved choice. Your choice never leaves your browser. <code>localStorage</code> is not a cookie.</p>
          <p><strong>The Copy button.</strong> On the main page, the Copy button puts the terminal command on your clipboard. It does this only when you click it. It never reads your clipboard.</p>
        </section>

        <section>
          <h2>What the site does not do</h2>
          <ul>
            <li>It has no accounts, no forms and no ads.</li>
            <li>It shows no cookie banner. Nothing on the site needs one.</li>
            <li>Your browser never calls the /dev/null API. The site gets the /dev/null profiles and posts when it is built, on GitHub Actions. The pages you get already hold them.</li>
          </ul>
        </section>

        <section>
          <h2>Your rights</h2>
          <p>Under the GDPR, you have these rights:</p>
          <ul>
            <li>to see the data held about you</li>
            <li>to have it corrected</li>
            <li>to have it erased</li>
            <li>to limit how it is used</li>
            <li>to object to its use, under Art. 21</li>
            <li>to complain to your local data protection authority</li>
          </ul>
          <p>The site itself keeps no data that identifies you. Cloudflare says that it discards your IP address at the nearest data center. So in most cases, there is nothing to show, correct or erase. You can still ask, at <a href="mailto:<?= $e($mail) ?>"><?= $e($mail) ?></a>.</p>
          <p>For the data that Cloudflare holds, its own policy explains your rights: the <a href="<?= $e($cf['policy']) ?>">Cloudflare privacy policy</a>.</p>
        </section>

        <section>
          <h2>Links to other sites</h2>
          <p>Each entry links to its own source, often on GitHub. The site also links to dev-null.sh, whyphp.dev, albertoarena.it and cogway.dev. Those sites are not part of phplives.dev. This page does not cover what they collect.</p>
        </section>

        <section>
          <h2>Changes to this page</h2>
          <p>When the site changes what it collects, this page changes too. The date below shows the last change.</p>
        </section>
      </div>
      <div class="sheet-foot"><span class="updated">Last updated: <span class="mono">17/10/2026</span></span></div>
    </article>
  </div>
