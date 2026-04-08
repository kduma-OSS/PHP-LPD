<?php

declare(strict_types=1);

namespace KDuma\LPD\Client;


class Configuration
{
    const LPD_DEFAULT_PORT   = 515;
    const ONE_MINUTE         = 60;
    const DEFAULT_QUEUE_NAME = 'default';

    protected int $port = self::LPD_DEFAULT_PORT;
    protected string $address;
    protected string $queue = self::DEFAULT_QUEUE_NAME;
    protected int $timeout = self::ONE_MINUTE;

    public function __construct(string $address, string $queue = self::DEFAULT_QUEUE_NAME, int $port = self::LPD_DEFAULT_PORT, int $timeout = self::ONE_MINUTE)
    {
        $this->port = $port;
        $this->address = $address;
        $this->queue = $queue;
        $this->timeout = $timeout;
    }

    public static function make(string $address, string $queue = self::DEFAULT_QUEUE_NAME, int $port = self::LPD_DEFAULT_PORT, int $timeout = self::ONE_MINUTE): self
    {
        return new self($address, $queue, $port, $timeout);
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}