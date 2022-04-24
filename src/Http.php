<?php

namespace App\Http;

use GuzzleHttp\Client;

const URL = 'https://cv.hexlet.io';

function requestAndGetContents(string $url): string
{
    $client = new Client(['base_uri' => URL]);

    $response = $client->request('GET', $url);

    return $response->getBody()->getContents();
}
