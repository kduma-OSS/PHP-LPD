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

    /**
     * @var resource|null
     */
    private $socket = null;

    /**
     * @var null
     */
    private $handler = null;

    /**
     * @var string
     */
    private $address = '127.0.0.1';

    /**
     * @var int
     */
    private $port = self::LPD_DEFAULT_PORT;

    /**
     * @var int
     */
    private $max_connections = 5;

    /**
     * @param null $handler
     *
     * @return Server
     */
    public function setHandler($handler): Server
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @param string $address
     *
     * @return Server
     */
    public function setAddress(string $address): Server
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @param int $port
     *
     * @return Server
     */
    public function setPort(int $port): Server
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param int $max_connections
     *
     * @return Server
     */
    public function setMaxConnections(int $max_connections): Server
    {
        $this->max_connections = $max_connections;
        return $this;
    }

    /**
     *
     */
    public function __destruct()
    {
        @socket_close($this->socket);
    }

    /**
     * @throws SocketErrorException
     */
    public function run()
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
     * @param      $msgsock
     * @param bool $receive_mode
     * @param null $control_file
     *
     * @throws SocketErrorException
     * @throws Exception
     */
    protected function read_command($msgsock, $receive_mode = false, $control_file = null)
    {
        if (false === ($buff = socket_read($msgsock, 4096, PHP_NORMAL_READ))) {
            throw new SocketErrorException('socket_read() failed: reason: ' . socket_strerror(socket_last_error($msgsock)));
        }
        $command = ord($buff[0]);
        $arguments = preg_split('([\s]+)', substr($buff, 1));
        $this->process_command($msgsock, $command, $arguments, $receive_mode, $control_file);
    }

    /**
     * @param $msgsock
     * @param $bytes
     *
     * @return string
     * @throws SocketErrorException
     */
    protected function read_bytes($msgsock, $bytes)
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
     * @param      $msgsock
     * @param      $command
     * @param      $arguments
     * @param      $receive_mode
     * @param null $control_file
     *
     * @throws Exception
     */
    protected function process_command($msgsock, $command, $arguments, $receive_mode, $control_file = null)
    {
        $this->debug($command);
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

    /**
     * @param $data
     * @param $control_file
     */
    protected function process_data($data, $control_file)
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