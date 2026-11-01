<?php
/**
 * The search field: a paper slip above the filters. The script shows it, like the
 * other tools, and filters the entries of the page as the visitor types.
 */
?>
<div class="search" role="search" data-tools hidden>
  <svg class="search-glass" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5 21 21"/></svg>
  <label for="search-q">Search</label>
  <input id="search-q" type="search" data-search placeholder="Project, maker or word" autocomplete="off" spellcheck="false" enterkeyhint="search" aria-keyshortcuts="/">
  <kbd class="search-key mono" data-search-key aria-hidden="true">/</kbd>
  <button type="button" class="search-clear" data-search-clear hidden>Clear</button>
</div>
