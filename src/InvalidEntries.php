<?php

declare(strict_types=1);

namespace PhpLives;

use RuntimeException;

final class InvalidEntries extends RuntimeException
{
    /**
     * @param  array<string, list<string>>  $errors  Messages, keyed by file name.
     */
    public function __construct(public readonly array $errors)
    {
        $lines = [];

        foreach ($errors as $file => $messages) {
            foreach ($messages as $message) {
                $lines[] = "{$file}: {$message}";
            }
        }

        parent::__construct("Some entries are not valid:\n".implode("\n", $lines));
    }
}
