<?php

declare(strict_types=1);

namespace KDuma\LPD\Client\Jobs;


class TextJob implements JobInterface
{
    /**
     * @var string|string
     */
    protected $content;

    /**
     * Job constructor.
     *
     * @param string $content
     */
    public function __construct($content = "")
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return TextJob
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return TextJob
     */
    public function appdendContent($content): self
    {
        $this->content .= $content;

        return $this;
    }

    /**
     * @return int
     */
    public function getContentLength()
    {
        return strlen($this->content);
    }

    /**
     * @param resource $stream
     * @param callable $debug
     */
    public function streamContent($stream, $debug)
    {
        fwrite($stream, $this->getContent());
    }

    /**
     * @param $error_message
     * @param $error_number
     *
     * @return bool|string
     */
    public function isValid(&$error_message, &$error_number)
    {
        return true;
    }
}