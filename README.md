# PHP-LPD

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

LPD Server and Client for PHP language.

Check full documentation here: [opensource.duma.sh/libraries/php/lpd](https://opensource.duma.sh/libraries/php/lpd)

## Install

Via Composer

```bash
$ composer require kduma/lpd
```

## Usage

### Server
``` php
(new KDuma\LPD\Server\Server())
	->setAddress($address)
	->setPort($port)
	->setMaxConnections($max_connections)
	->setHandler(function ($incoming_data, $ctrl) {
		echo $incoming_data; // Do something with it!
	})
	->run();
```

### Client

#### Text print job

For printing clear text use `TextJob` class:
``` php
$job = new KDuma\LPD\Client\Jobs\TextJob("This is content!");
$job->appdendContent("\n");
$job->appdendContent("And this is second line.");
```

#### File print job

For printing files, text or binary, use `FileJob` class:
``` php
$job = new KDuma\LPD\Client\Jobs\FileJob("my_raw_file.txt");
```

#### Print Service

``` php
$configuration = new KDuma\LPD\Client\Configuration($address, $queue_name, $port, $timeout);

$print_service = new KDuma\LPD\Client\PrintService($configuration);

$print_service->sendJob($job);
```

# Original Attribution

This package is based on classes created by [Ivan Bozhanov](https://github.com/vakata) 
([server](https://github.com/vakata/php-lpd/blob/master/class.lpd.php), 2013) 
and Mick Sear 
([client](https://github.com/vakata/php-lpd/blob/master/example/class.lpr.php), 2005).




[ico-version]: https://img.shields.io/packagist/v/kduma/lpd.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/kduma/lpd.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/kduma/lpd
[link-downloads]: https://packagist.org/packages/kduma/lpd
