<?php

return [
    'name' => 'Neuron AI 4.0',
    'sentence' => 'A new major version of Neuron AI, the PHP framework for AI agents, with a rebuilt Workflow engine and faster streaming.',
    'details' => 'Streaming Channels send an agent\'s output to the browser through Redis or Pusher, even from a background worker. A new long-term memory lets an agent recall a user\'s past conversations. Events now go through a standard PSR-14 event dispatcher.',
    'makers' => [
        ['name' => 'Valerio Barbera', 'github' => 'ilvalerione'],
        ['name' => 'Neuron', 'github' => 'neuron-core'],
    ],
    'shipped' => '2026-09-30',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/neuron-core/neuron-ai/releases/tag/4.0.0',
    ],
    'source' => 'https://github.com/neuron-core/neuron-ai',
    'package' => 'neuron-core/neuron-ai',
    'licence' => 'MIT',
    'docs' => 'https://docs.neuron-ai.dev/',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: Neuron AI 4.0. A new major version of Neuron AI, the PHP framework for AI agents, with a rebuilt Workflow engine and faster streaming. https://github.com/neuron-core/neuron-ai'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-22',
];
