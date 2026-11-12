<?php

return [
    'name' => 'devnull-stream',
    'sentence' => 'A PHP stream wrapper for dev-null.sh that lets you read, commit, reply and star with plain file functions such as file_get_contents() and touch().',
    'details' => 'Commits read like git log. Writes are signed with an SSH key of your GitHub account, so there is no sign-up and no token. It has no dependency beyond PHP itself.',
    'makers' => [
        ['name' => 'Julien Bianchi', 'github' => 'jubianchi'],
    ],
    'shipped' => '2026-10-23',
    'proof' => [
        'kind' => 'tag',
        'url' => 'https://github.com/jubianchi/devnull-stream/tree/v0.1.0',
    ],
    'source' => 'https://github.com/jubianchi/devnull-stream',
    'package' => 'jubianchi/devnull-stream',
    'licence' => 'MIT',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: devnull-stream. A PHP stream wrapper for dev-null.sh that lets you read, commit, reply and star with plain file functions such as file_get_contents() and touch(). https://github.com/jubianchi/devnull-stream'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-12',
    'added' => '2026-11-12',
];
