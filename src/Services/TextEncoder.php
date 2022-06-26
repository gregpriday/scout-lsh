<?php

namespace SiteOrigin\ScoutLSH\Services;

use GuzzleHttp\Client;

class TextEncoder
{
    public function __construct(string $endpoint)
    {
        $this->client = new Client([
            'base_uri' => $endpoint,
        ]);
    }

    public function encode(array $texts): array
    {
        $keys = array_keys($texts);

        $response = $this->client->post('/', [
            'json' => [
                'texts' => array_values($texts),
            ],
        ]);

        $encoded = json_decode($response->getBody())->encoded;
        return array_combine($keys, $encoded);
    }
}
