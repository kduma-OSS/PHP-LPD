# PHP LPD

LPD (Line Printer Daemon) client and server implementation in pure PHP.

Full documentation: [opensource.duma.sh/libraries/php/lpd](https://opensource.duma.sh/libraries/php/lpd)

## Requirements

- PHP `^8.3`

## Installation

```bash
composer require kduma/lpd
```

## Usage

### Client

```php
$configuration = new KDuma\LPD\Client\Configuration(
    address: '192.168.1.100',
    queue: 'PASSTHRU',
    port: 515,
    timeout: 30,
);

$job = new KDuma\LPD\Client\Jobs\TextJob("Hello, Printer!");

(new KDuma\LPD\Client\PrintService($configuration))->sendJob($job);
```

### Server

```php
(new KDuma\LPD\Server\Server())
    ->setAddress('0.0.0.0')
    ->setPort(515)
    ->setHandler(function (string $data, mixed $ctrl): void {
        echo $data;
    })
    ->run();
```
