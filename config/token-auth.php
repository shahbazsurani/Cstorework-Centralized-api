<?php

return [
    // Sliding expiration total Time-To-Live for Sanctum personal access tokens (in minutes)
    'token_ttl_minutes' => env('TOKEN_TTL_MINUTES', 30),

    // Renew the current token when remaining lifetime is less than or equal to this many minutes
    'token_renew_before_minutes' => env('TOKEN_SLIDING_RENEW_BEFORE_MINUTES', 15),
];
