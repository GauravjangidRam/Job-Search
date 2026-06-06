<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hashids Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration is used for generating encrypted IDs for URLs.
    | Use a consistent key across all environments for hash decoding to work.
    |
    */

    'key' => env('HASHIDS_KEY', 'your-default-secret-key-for-hashids'),
];
