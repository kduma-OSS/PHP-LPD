<?php

declare(strict_types=1);

namespace KDuma\LPD\Client\Jobs;


interface JobInterface
{
    /**
     * @return int
     */
    public function getContentLength();

    /**
     * @param string $error_message
     * @param int    $error_number
     *
     * @return bool
     */
    public function isValid(&$error_message, &$error_number);

    /**
     * @param resource $stream
     * @param callable $debug
     */
    public function streamContent($stream, $debug);
}