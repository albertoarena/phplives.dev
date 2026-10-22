<?php

return [
    'name' => 'Lean package validator 6.0',
    'sentence' => 'A new major version of the lean package validator, a CLI that makes sure a package keeps its repo files out of release archives.',
    'details' => 'Version 6.0 also validates and reformats negated export-ignore lines in .gitattributes. A new --flavour option changes how the create command writes that file. Agent detection is now automatic, so the --agentic-run option is gone.',
    'makers' => [
        ['name' => 'Raphael Stolt', 'github' => 'raphaelstolt'],
    ],
    'shipped' => '2026-06-01',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/raphaelstolt/lean-package-validator/releases/tag/v6.0.0',
    ],
    'source' => 'https://github.com/raphaelstolt/lean-package-validator',
    'package' => 'stolt/lean-package-validator',
    'licence' => 'MIT',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: Lean package validator 6.0. A new major version of the lean package validator, a CLI that makes sure a package keeps its repo files out of release archives. https://github.com/raphaelstolt/lean-package-validator'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-22',
];
