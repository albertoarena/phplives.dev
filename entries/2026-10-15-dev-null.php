<?php

return [
    'name' => 'dev/null',
    'sentence' => 'A social network for developers: sign in with GitHub, follow developers and share commits.',
    // Makers, checked 17/10/2026. Nuno Maduro led the build on his own channel:
    // https://www.youtube.com/watch?v=6zCNQa3hNm0 (day 1, 15/10/2026) and
    // https://www.youtube.com/watch?v=nfmVdXZfzUE (day 2, 16/10/2026). The other four are the
    // top contributors in the repo's commit history on those two days:
    // https://github.com/nunomaduro/dev-null.sh/graphs/contributors
    'makers' => [
        ['name' => 'Nuno Maduro', 'github' => 'nunomaduro'],
        ['name' => 'Márk Magyar', 'github' => 'xHeaven'],
        ['name' => 'Matthieu Napoli', 'github' => 'mnapoli'],
        ['name' => 'Brent Roose', 'github' => 'brendt'],
        ['name' => 'Pauline Vos', 'github' => 'paulinevos'],
    ],
    'shipped' => '2026-10-15',
    'proof' => [
        'kind' => 'post',
        'url' => 'https://dev-null.sh/@nunomaduro/commits/01a13fae-4146-7221-a761-90f8deabd62b',
    ],
    'source' => 'https://github.com/nunomaduro/dev-null.sh',
    'stack' => 'laravel',
    'devnull' => 'Shipped with PHP: dev/null. A social network for developers: sign in with GitHub, follow developers and share commits. https://github.com/nunomaduro/dev-null.sh'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-17',
];
