<?php

declare(strict_types=1);

namespace KDuma\LPD\Client\Jobs;


interface JobInterface
{
    public function getContentLength(): int;

    public function isValid(string &$error_message, int &$error_number): bool;

    public function streamContent(mixed $stream, callable $debug): void;
}