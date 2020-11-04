# http-client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/huangdijia/http-client.svg?style=flat-square)](https://packagist.org/packages/huangdijia/http-client)
[![Total Downloads](https://img.shields.io/packagist/dt/huangdijia/http-client.svg?style=flat-square)](https://packagist.org/packages/huangdijia/http-client)
[![GitHub license](https://img.shields.io/github/license/huangdijia/http-client)](https://github.com/huangdijia/http-client)

HTTP client base curl, like laravel HTTP client.

## Installation

## Quick start

### GET

~~~php
use Huangdijia\Http\Client;

$response = Client::get($url);
~~~

### POST

~~~php
use Huangdijia\Http\Client;

$response = Client::post($url, $data);
~~~

### UPLOAD

~~~php
use Huangdijia\Http\Client;

$response = Client::post($url, ['file' => curl_file_create($fileRealpath)]);
~~~

### Other uses

[Laravel HTTP Client](https://laravel.com/docs/8.x/http-client)

[Laravel HTTP 客户端](https://learnku.com/docs/laravel/8.x/http-client/9394)
