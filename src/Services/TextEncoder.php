<?php

namespace SiteOrigin\ScoutLSH\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class TextEncoder
{
    public function __construct(string $endpoint)
    {
        $this->client = new Client([
            'base_uri' => $endpoint,
        ]);
    }

    public function encode(array $texts, bool $cache = false): array
    {
        if ($cache) {
            $inputs = collect($texts)
                ->mapWithKeys(fn ($t) => [config('lsh.query.cache_key').'::'.md5($t) => $t])->toArray();

            // First try get all the texts from the cache.
            $encoded = Cache::rememberMultiple($inputs, null, function ($inputs) {
                return $this->encodeTexts($inputs);
            });
        } else {
            $encoded = $this->encodeTexts($texts);
        }

        return array_values(array_map(
            fn ($e) => array_map(
                fn ($c) => base_convert(implode('', $c), 16, 10),
                array_chunk(str_split($e), 16)
            ),
            $encoded
        ));
    }

    /**
     * @param array $texts
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function encodeTexts(array $texts): array
    {
        $keys = array_keys($texts);

        $response = $this->client->post('', [
            'json' => [
                'texts' => array_values($texts),
            ],
        ]);

        $encoded = json_decode($response->getBody())->encoded;

        return array_combine($keys, $encoded);
    }
}
