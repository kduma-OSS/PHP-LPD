<?php

declare(strict_types=1);

namespace KDuma\LPD\Client\DebugHandler;


class BasicDebugHandler
{
    /** @var string[] */
    protected array $messages = [];

    public function __invoke(string $message): void
    {
        $this->messages[] = $message;
    }

    public function clearLog(): void
    {
        $this->messages = [];
    }

    public function getLog(): string
    {
        return implode("\n", $this->messages);
    }
}