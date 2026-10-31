<?php

return [
    'name' => 'Rubix ML 3.0',
    'sentence' => 'A new major version of Rubix ML, a machine learning and deep learning library for PHP, with more of its models now running in parallel.',
    'details' => 'K Nearest Neighbors and Isolation Forest now run in parallel. Neural networks can stream snapshots to disk. More learners use a validation set, and AdaBoost stops early when it no longer improves.',
    'makers' => [
        ['name' => 'Rubix ML', 'github' => 'RubixML'],
        ['name' => 'Andrew DalPino', 'github' => 'andrewdalpino'],
    ],
    'shipped' => '2026-10-08',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/RubixML/ML/releases/tag/3.0.0',
    ],
    'source' => 'https://github.com/RubixML/ML',
    'package' => 'rubix/ml',
    'licence' => 'MIT',
    'docs' => 'https://rubixml.org/',
    'stack' => 'php',
    'topics' => ['ai'],
    'devnull' => 'Shipped with PHP: Rubix ML 3.0. A new major version of Rubix ML, a machine learning and deep learning library for PHP, with more of its models now running in parallel. https://github.com/RubixML/ML'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-30',
    'added' => '2026-10-31',
];
