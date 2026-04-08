<?php

declare(strict_types=1);

namespace KDuma\LPD\Client;


use KDuma\LPD\Client\Exceptions\InvalidJobException;
use KDuma\LPD\Client\Exceptions\PrintErrorException;
use KDuma\LPD\Client\Jobs\JobInterface;
use KDuma\LPD\DebugHandlerTrait;

class PrintService
{
    use DebugHandlerTrait;

    protected Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @throws PrintErrorException
     * @throws InvalidJobException
     */
    public function sendJob(JobInterface $job): void
    {
        $error_message = '';
        $error_number = 0;
        if (!$job->isValid($error_message, $error_number))
            throw new InvalidJobException($error_message, $error_number);

        //Private static function prints waiting jobs on the queue.
        $this->printWaiting();

        //Open a new connection to send the control file and data.
        $stream = stream_socket_client(
            sprintf("tcp://%s:%s", $this->configuration->getAddress(), $this->configuration->getPort()),
            $error_number,
            $error_message,
            $this->configuration->getTimeout()
        );

        if (!$stream)
            throw new PrintErrorException($error_message, $error_number);

        $jobId = self::getJobId();

        //Set printer to receive file
        fwrite($stream, sprintf("%s%s\n", chr(2), $this->configuration->getQueue()));
        $this->debug("Confirmation of receive cmd:" . ord(fread($stream, 1)));

        //Send Control file.
        $server = $this->getServerName();
        $ctrl = sprintf("H%s\nPphp\nfdfA%s%s\n", $server, $jobId, $server);
        fwrite($stream, sprintf("%s%s cfA%s%s\n", chr(2), strlen($ctrl), $jobId, $server));
        $this->debug("Confirmation of sending of control file cmd:" . ord(fread($stream, 1)));

        fwrite($stream, sprintf("%s%s", $ctrl, chr(0))); //Write null to indicate end of stream
        $this->debug("Confirmation of sending of control file itself:" . ord(fread($stream, 1)));


        fwrite($stream, sprintf("%s%s dfA%s%s\n", chr(3), $job->getContentLength(), $jobId, $server));
        $this->debug("Confirmation of sending receive data cmd:" . ord(fread($stream, 1)));

        $job->streamContent($stream, function (string $message): void {
            $this->debug($message);
        });

        fwrite($stream, chr(0));//Write null to indicate end of stream
        $this->debug("Confirmation of sending data:" . ord(fread($stream, 1)));
    }

    /**
     * @throws PrintErrorException
     */
    private function printWaiting(): void
    {
        $error_message = '';
        $error_number = 0;
        $stream = stream_socket_client(
            sprintf("tcp://%s:%s", $this->configuration->getAddress(), $this->configuration->getPort()),
            $error_number,
            $error_message,
            $this->configuration->getTimeout()
        );

        if (!$stream)
            throw new PrintErrorException($error_message, $error_number);

        //Print any waiting jobs
        fwrite($stream, sprintf("%s%s\n", chr(1), $this->configuration->getQueue()));
        while (!feof($stream))
            fread($stream, 1);

    }

    private static function getJobId(): string
    {
        return "001";
    }

    private function getServerName(): string
    {
        return (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : "me";
    }

}