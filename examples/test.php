<?php

require __DIR__ . '/../vendor/autoload.php';

use Huangdijia\Http\Client;

$response = Client::withCookies(['a' => 1, 'b' => 2], '')
    // ->asJson()
    // ->asForm()
    ->withToken('abc')
    ->withHeaders(['My-Header' => 'haha'])
    ->withHeaders(['My-Version' => '1.0'])
    ->post('http://localhost:8000/server.php', [
        'q1' => 1,
        'file' => curl_file_create(__DIR__ . '/server.php'),
    ]);

var_dump($response->json());
