<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for new businesses and orders. The launch country
    | has not been decided yet, so this is a single place to change.
    |
    */

    'currency' => 'PHP',

    /*
    |--------------------------------------------------------------------------
    | Flat Shipping
    |--------------------------------------------------------------------------
    |
    | Charged once per seller order. Kept in minor units so no rounding is
    | introduced by the price calculation.
    |
    */

    'flat_shipping_minor' => 5000,

    /*
    |--------------------------------------------------------------------------
    | Honey Points Earning
    |--------------------------------------------------------------------------
    |
    | Points earned per whole currency unit spent. Points are recorded in the
    | ledger, so this only controls the size of each award.
    |
    */

    'points_per_currency_unit' => 1,

    /*
    |--------------------------------------------------------------------------
    | Welcome Bonus
    |--------------------------------------------------------------------------
    |
    | Awarded once when a customer completes registration.
    |
    */

    'welcome_bonus_points' => 100,

    /*
    |--------------------------------------------------------------------------
    | Review Reward
    |--------------------------------------------------------------------------
    |
    | Awarded for a published review that is a verified purchase.
    |
    */

    'review_reward_points' => 25,

    /*
    |--------------------------------------------------------------------------
    | Review Rate Limit
    |--------------------------------------------------------------------------
    |
    | Maximum reviews a customer may submit per minute. Editing an existing
    | rating still counts towards the limit.
    |
    */

    'review_rate_limit_per_minute' => (int) env('REVIEW_RATE_LIMIT_PER_MINUTE', 10),

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    |
    | The gateway used at checkout. Card data never touches our servers: a
    | real gateway redirects the customer and we only store the result.
    |
    | The payout model (platform collects then pays out manually, cash on
    | delivery, or split payments) is still undecided, so the default
    | collects the order without taking payment details.
    |
    */

    'payment_gateway' => 'offline',

];
