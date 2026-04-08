<?php

declare(strict_types=1);

namespace KDuma\LPD;


use KDuma\LPD\Client\PrintService;

trait DebugHandlerTrait
{
    /**
     * @var callable
     */
    protected $debug_handler;

    /**
     * @param callable $debug_handler
     *
     * @return self
     */
    public function setDebugHandler($debug_handler): self
    {
        $this->debug_handler = $debug_handler;

        return $this;
    }

    /**
     * @param $message
     */
    protected function debug($message)
    {
        if ($this->debug_handler) {
            $handler = $this->debug_handler;
            $handler($message);
        }
    }
}