<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chat Rate Limit
    |--------------------------------------------------------------------------
    |
    | Maximum messages a user may send per minute across all conversations.
    | Protects the inbox from flooding without affecting normal use.
    |
    */

    'rate_limit_per_minute' => (int) env('CHAT_RATE_LIMIT_PER_MINUTE', 20),

];
