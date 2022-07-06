<?php
// config for SiteOrigin/ScoutLSH
return [
    'encoder' => 'http://127.0.0.1:5000/encode',
    'model' => 'default',
    'search_candidates' => 500,
    'query' => [
        'cache_duration' => 43200,
        'cache_key' => 'lsh-encoded-cache',
    ],
    'autolink' => [
        'threshold' => 0.65,
        'models' => null,
        'weights' => ['title' => 1, 'content' => 0.5],
    ]
];
