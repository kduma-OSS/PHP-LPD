<?php

declare(strict_types=1);

namespace KDuma\LPD\Client\DebugHandler;


class BasicDebugHandler
{
    /**
     * @var string[]
     */
    protected $messages = [];

    /**
     * @param $message
     */
    public function __invoke($message)
    {
        $this->messages[] = $message;
    }

    public function clearLog()
    {
        $this->messages = [];
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return implode("\n", $this->messages);
    }
}