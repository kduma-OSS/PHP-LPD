<?php

declare(strict_types=1);

namespace KDuma\LPD\Client\Jobs;


class FileJob implements JobInterface
{
    protected string $file_name;

    public function __construct(string $file_name)
    {
        $this->file_name = $file_name;
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function getContentLength(): int
    {
        return (int) filesize($this->file_name);
    }

    public function streamContent(mixed $stream, callable $debug): void
    {
        $handler = $this->getFileHandler($debug);

        while (!feof($handler)) {
            fwrite($stream, fread($handler, 8192));
        }

        fclose($handler);
    }

    private function getFileHandler(callable $debug): mixed
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $debug("Operating system is Windows");
            //Force binary in Windows.
            return fopen($this->file_name, "rb");
        }

        $debug("Operating system is not Windows");
        return fopen($this->file_name, "r");
    }

    public function isValid(string &$error_message, int &$error_number): bool
    {
        if (is_readable($this->file_name))
            return true;

        $error_message = "File is not readable!";
        $error_number = 404;

        return false;
    }
}