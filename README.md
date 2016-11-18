# Flysystem HTTP Adapter

[![Author](http://img.shields.io/badge/author-@chrisleppanen-blue.svg?style=flat-square)](https://twitter.com/chrisleppanen)
[![Build Status](https://img.shields.io/travis/twistor/flysystem-http/master.svg?style=flat-square)](https://travis-ci.org/twistor/flysystem-http)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/twistor/flysystem-http.svg?style=flat-square)](https://scrutinizer-ci.com/g/twistor/flysystem-http/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/twistor/flysystem-http.svg?style=flat-square)](https://scrutinizer-ci.com/g/twistor/flysystem-http)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/twistor/flysystem-http.svg?style=flat-square)](https://packagist.org/packages/twistor/flysystem-http)

This adapter uses basic PHP functions to access HTTP resources. It is read only.

## Installation

```bash
composer require twistor/flysystem-http
```

## Usage

```php
use League\Flysystem\Filesystem;
use Twistor\Flysystem\Http\HttpAdapter;

$filesystem = new Filesystem(new HttpAdapter('http://example.com'));

$contents = $filesystem->read('file.txt');
```

By default, metadata will be retrieved via HEAD requests. This can be disabled.

```php
use Twistor\Flysystem\Http\HttpAdapter;

$supportsHead = false;

$adapter = new HttpAdapter('http://example.com', $supportsHead);
```

PHP context options can be set using the third parameter.
```php
use Twistor\Flysystem\Http\HttpAdapter;

$context = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
];

$adapter = new HttpAdapter('http://example.com', true, $context);
```
