<?php
/**
 * What shows in place of the entries when nothing matches the search and the filters.
 * The script writes the title, with the words the visitor typed.
 */
?>
<div class="search-none" data-search-none hidden>
  <h3 data-search-none-title>No project matches.</h3>
  <p>Check the spelling, or try a shorter word.</p>
  <div class="search-none-acts">
    <button type="button" data-search-reset>Clear the search</button>
    <a href="https://github.com/albertoarena/phplives.dev/issues/new?template=suggest-a-project.yml">Is a project missing? Suggest it in an issue</a>
  </div>
</div>
