<?php

return [
    'name' => 'Tally',
    'sentence' => 'A benchmarking tool for PHP that counts instructions, CPU cycles and memory instead of measuring time.',
    'details' => 'Timing benchmarks give a different result on every CI runner. Tally runs each benchmark once under CodSpeed\'s CPU simulation, which is built on Valgrind, so the numbers barely move between runs. The expected numbers are committed next to the benchmark and checked in every pull request.',
    'makers' => [
        ['name' => 'Ondřej Mirtes', 'github' => 'ondrejmirtes'],
    ],
    'shipped' => '2026-11-14',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/phpstan/tally/releases/tag/1.0.0',
    ],
    'source' => 'https://github.com/phpstan/tally',
    'package' => 'phpstan/tally',
    'licence' => 'MIT',
    'stack' => 'php',
    'topics' => ['testing', 'devtools'],
    'devnull' => 'Shipped with PHP: Tally. A benchmarking tool for PHP that counts instructions, CPU cycles and memory instead of measuring time. https://github.com/phpstan/tally'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-21',
    'added' => '2026-11-21',
];
