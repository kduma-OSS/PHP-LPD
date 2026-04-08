<?php

declare(strict_types=1);

namespace KDuma\LPD\Client;


class Configuration
{
    const LPD_DEFAULT_PORT   = 515;
    const ONE_MINUTE         = 60;
    const DEFAULT_QUEUE_NAME = 'default';

    /**
     * @var integer LPD Port
     */
    protected $port = self::LPD_DEFAULT_PORT;

    /**
     * @var string LPD Address
     */
    protected $address;

    /**
     * @var string LPD Queue Name
     */
    protected $queue = self::DEFAULT_QUEUE_NAME;

    /**
     * @var string Timeout in seconds
     */
    protected $timeout = self::ONE_MINUTE;

    /**
     * LPDPrinterConfiguration constructor.
     *
     * @param string $address Address
     * @param string $queue   Queue Name
     * @param int    $port    Port
     * @param int    $timeout
     */
    public function __construct($address, $queue = self::DEFAULT_QUEUE_NAME, $port = self::LPD_DEFAULT_PORT, $timeout = self::ONE_MINUTE)
    {
        $this->port = $port;
        $this->address = $address;
        $this->queue = $queue;
        $this->timeout = $timeout;
    }

    /**
     * @param string $address
     * @param string $queue
     * @param int    $port
     * @param int    $timeout
     *
     * @return Configuration
     */
    public static function make($address, $queue = self::DEFAULT_QUEUE_NAME, $port = self::LPD_DEFAULT_PORT, $timeout = self::ONE_MINUTE)
    {
        return new self($address, $queue, $port, $timeout);
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return string
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}