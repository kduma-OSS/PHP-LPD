<?php

declare(strict_types=1);

namespace KDuma\LPD\Client\Jobs;


class TextJob implements JobInterface
{
    protected string $content;

    public function __construct(string $content = "")
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function appdendContent(string $content): self
    {
        $this->content .= $content;

        return $this;
    }

    public function getContentLength(): int
    {
        return strlen($this->content);
    }

    public function streamContent(mixed $stream, callable $debug): void
    {
        fwrite($stream, $this->getContent());
    }

    public function isValid(string &$error_message, int &$error_number): bool
    {
        return true;
    }
}