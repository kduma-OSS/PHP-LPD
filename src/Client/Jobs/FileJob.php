<?php

declare(strict_types=1);

namespace KDuma\LPD\Client\Jobs;


class FileJob implements JobInterface
{
    /**
     * @var string
     */
    protected $file_name;

    /**
     * FileJob constructor.
     *
     * @param string $file_name
     */
    public function __construct($file_name)
    {
        $this->file_name = $file_name;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @return int
     */
    public function getContentLength()
    {
        return filesize($this->file_name);
    }

    /**
     * @param resource $stream
     * @param callable $debug
     */
    public function streamContent($stream, $debug)
    {
        $handler = $this->getFileHandler($debug);

        while (!feof($handler)) {
            fwrite($stream, fread($handler, 8192));
        }

        fclose($handler);
    }

    /**
     * @param callable $debug
     *
     * @return resource
     */
    private function getFileHandler($debug)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $debug("Operating system is Windows");
            //Force binary in Windows.
            return fopen($this->file_name, "rb");
        }

        $debug("Operating system is not Windows");
        return fopen($this->file_name, "r");
    }

    /**
     * @param $error_message
     * @param $error_number
     *
     * @return bool
     */
    public function isValid(&$error_message, &$error_number)
    {
        if (is_readable($this->file_name))
            return true;

        $error_message = "File is not readable!";
        $error_number = 404;

        return false;
    }
}