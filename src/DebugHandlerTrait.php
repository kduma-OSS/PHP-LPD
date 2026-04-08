<?php

declare(strict_types=1);

namespace KDuma\LPD;


use KDuma\LPD\Client\PrintService;

trait DebugHandlerTrait
{
    protected ?callable $debug_handler = null;

    public function setDebugHandler(callable $debug_handler): self
    {
        $this->debug_handler = $debug_handler;

        return $this;
    }

    protected function debug(string $message): void
    {
        if ($this->debug_handler) {
            $handler = $this->debug_handler;
            $handler($message);
        }
    }
}