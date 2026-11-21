<?php

return [
    'name' => 'NzoUrlEncryptorBundle 7.0',
    'sentence' => 'A new major version of NzoUrlEncryptorBundle, a Symfony bundle that encrypts the values you put in a URL and decrypts them for your controller.',
    'details' => 'Version 7 signs every encrypted value, so a value that was modified is now refused instead of being decrypted to something else. A context ties a value to one kind of link, a user or a record. An option still reads values encrypted by version 6 while you move.',
    'makers' => [
        ['name' => 'Ala Eddine Khefifi', 'github' => 'nayzo'],
    ],
    'shipped' => '2026-10-13',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/nayzo/NzoUrlEncryptorBundle/releases/tag/v7.0.0',
    ],
    'source' => 'https://github.com/nayzo/NzoUrlEncryptorBundle',
    'package' => 'nzo/url-encryptor-bundle',
    'licence' => 'MIT',
    'stack' => 'symfony',
    'topics' => ['security'],
    'devnull' => 'Shipped with PHP: NzoUrlEncryptorBundle 7.0. A new major version of NzoUrlEncryptorBundle, a Symfony bundle that encrypts the values you put in a URL and decrypts them for your controller. https://github.com/nayzo/NzoUrlEncryptorBundle'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-21',
    'added' => '2026-11-21',
];
