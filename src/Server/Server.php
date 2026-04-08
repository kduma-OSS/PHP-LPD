<?php

declare(strict_types=1);

namespace KDuma\LPD\Server;

use Exception;
use KDuma\LPD\DebugHandlerTrait;
use KDuma\LPD\Server\Exceptions\SocketErrorException;

class Server
{
    const LPD_DEFAULT_PORT = 515;

    use DebugHandlerTrait;

    private mixed $socket = null;
    private ?callable $handler = null;
    private string $address = '127.0.0.1';
    private int $port = self::LPD_DEFAULT_PORT;
    private int $max_connections = 5;

    public function setHandler(?callable $handler): Server
    {
        $this->handler = $handler;
        return $this;
    }

    public function setAddress(string $address): Server
    {
        $this->address = $address;
        return $this;
    }

    public function setPort(int $port): Server
    {
        $this->port = $port;
        return $this;
    }

    public function setMaxConnections(int $max_connections): Server
    {
        $this->max_connections = $max_connections;
        return $this;
    }

    public function __destruct()
    {
        @socket_close($this->socket);
    }

    /**
     * @throws SocketErrorException
     */
    public function run(): void
    {
        if (($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            throw new SocketErrorException('socket_create() failed: reason: ' . socket_strerror(socket_last_error()));
        }
        if (socket_bind($this->socket, $this->address, $this->port) === false) {
            throw new SocketErrorException('socket_bind() failed: reason: ' . socket_strerror(socket_last_error($this->socket)));
        }
        if (socket_listen($this->socket, $this->max_connections) === false) {
            throw new SocketErrorException('socket_listen() failed: reason: ' . socket_strerror(socket_last_error($this->socket)));
        }

        do {
            if (($msgsock = socket_accept($this->socket)) === false) {
                throw new SocketErrorException('socket_accept() failed: reason: ' . socket_strerror(socket_last_error($this->socket)));
            }
            $this->debug('New client');
            $this->read_command($msgsock);
        } while (true);
    }

    /**
     * @throws SocketErrorException
     * @throws Exception
     */
    protected function read_command(mixed $msgsock, bool $receive_mode = false, ?string $control_file = null): void
    {
        if (false === ($buff = socket_read($msgsock, 4096, PHP_NORMAL_READ))) {
            throw new SocketErrorException('socket_read() failed: reason: ' . socket_strerror(socket_last_error($msgsock)));
        }
        $command = ord($buff[0]);
        $arguments = preg_split('([\s]+)', substr($buff, 1));
        $this->process_command($msgsock, $command, $arguments, $receive_mode, $control_file);
    }

    /**
     * @throws SocketErrorException
     */
    protected function read_bytes(mixed $msgsock, mixed $bytes): string
    {
        $content = '';
        do {
            if (false === ($buff = socket_read($msgsock, 1024, PHP_BINARY_READ))) {
                throw new SocketErrorException('socket_read() failed: reason: ' . socket_strerror(socket_last_error($msgsock)));
            }
            $content .= $buff;
        } while (mb_strlen($content, '8bit') < $bytes && $buff != '');
        return $content;
    }

    /**
     * @throws Exception
     */
    protected function process_command(mixed $msgsock, int $command, array $arguments, bool $receive_mode, ?string $control_file = null): void
    {
        $this->debug((string) $command);
        switch ($command) {
            case 1:
                socket_write($msgsock, chr(0));
                socket_close($msgsock);
                break;
            case 2:
                if (!$receive_mode) {
                    $receive_mode = true;
                    socket_write($msgsock, chr(0));
                    $this->read_command($msgsock, $receive_mode);
                } else {
                    socket_write($msgsock, chr(0));
                    $control_file = $this->read_bytes($msgsock, $arguments[0]);
                    socket_write($msgsock, chr(0));
                    $this->read_command($msgsock, $receive_mode, $control_file);
                }
                break;
            case 3:
                if (!$receive_mode) {
                    socket_write($msgsock, chr(0));
                    $this->read_command($msgsock, $receive_mode);
                } else {
                    socket_write($msgsock, chr(0));
                    $data = $this->read_bytes($msgsock, $arguments[0]);
                    socket_write($msgsock, chr(0));
                    socket_close($msgsock);
                    $this->process_data($data, $control_file);
                }
                break;
            default:
                socket_write($msgsock, chr(0));
                break;
        }
    }

    protected function process_data(string $data, ?string $control_file): void
    {
        $data = preg_split('(\n)', $data);
        $dump = [];
        foreach ($data as $row) {
            $res = [];
            $row = preg_split('(\r)', $row);
            foreach ($row as $r) {
                for ($i = 0, $j = strlen($r); $i < $j; $i++) {
                    if (!isset($res[$i]) || $r[$i] !== ' ') {
                        $res[$i] = $r[$i];
                    }
                }
            }
            $dump[] = implode('', $res);
        }
        $dump = implode("\r\n", $dump);
        $data = $dump;

        if ($this->handler && is_callable($this->handler)) {
            call_user_func($this->handler, $data, $control_file);
        }
    }
}