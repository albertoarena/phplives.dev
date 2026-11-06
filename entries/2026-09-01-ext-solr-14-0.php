<?php

return [
    'name' => 'EXT:solr 14.0',
    'sentence' => 'The first stable version of EXT:solr for TYPO3 14 LTS, the extension that adds Apache Solr search to TYPO3.',
    'details' => 'It is also a security release, with fixes for five vulnerabilities reported through the TYPO3 Security Team. It needs Apache Solr 10. Indexing no longer sends HTTP requests to the site itself. The release notes say this makes it much faster.',
    'makers' => [
        ['name' => 'TYPO3-Solr', 'github' => 'TYPO3-Solr'],
        ['name' => 'Rafael Kähm', 'github' => 'dkd-kaehm'],
    ],
    'shipped' => '2026-09-01',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/TYPO3-Solr/ext-solr/releases/tag/14.0.0',
    ],
    'source' => 'https://github.com/TYPO3-Solr/ext-solr',
    'package' => 'apache-solr-for-typo3/solr',
    'licence' => 'GPL-3.0-or-later',
    'docs' => 'https://docs.typo3.org/p/apache-solr-for-typo3/solr/14.0/en-us/',
    'stack' => 'php',
    'topics' => ['cms'],
    'devnull' => 'Shipped with PHP: EXT:solr 14.0. The first stable version of EXT:solr for TYPO3 14 LTS, the extension that adds Apache Solr search to TYPO3. https://github.com/TYPO3-Solr/ext-solr'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-06',
    'added' => '2026-11-06',
];
