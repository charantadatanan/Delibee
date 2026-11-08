<?php

return [

    /*
    |--------------------------------------------------------------------------
    | BeeRank Window
    |--------------------------------------------------------------------------
    |
    | BeeRank scores a business on what it has done *recently*, not on its
    | lifetime totals. This is the whole point of the product: a new business
    | with two months of strong performance can outrank an established one
    | that has gone quiet.
    |
    */

    'window_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Minimum Orders
    |--------------------------------------------------------------------------
    |
    | A business needs at least this many orders inside the window before it
    | can be ranked at all. Without this floor a single lucky order would
    | produce a perfect score.
    |
    */

    'minimum_orders' => 5,

    /*
    |--------------------------------------------------------------------------
    | Component Weights
    |--------------------------------------------------------------------------
    |
    | The weights must add up to 1.0. Each component is normalised to the
    | 0..1 range before weighting.
    |
    | - rating          Bayesian-smoothed average of verified-purchase reviews
    | - completion      share of orders that were delivered rather than cancelled
    | - on_time         share of shipped orders delivered within the window
    | - response_time   how fast the seller answers in Bee Chat
    |
    */

    'weights' => [
        'rating' => 0.40,
        'completion' => 0.25,
        'on_time' => 0.20,
        'response_time' => 0.15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bayesian Smoothing
    |--------------------------------------------------------------------------
    |
    | A business with two five-star reviews must not outrank one with fifty
    | four-star reviews, so the raw average is pulled towards a prior mean
    | using the global average rating and a virtual review count.
    |
    */

    'smoothing' => [
        'prior_mean' => 4.0,
        'prior_weight' => 5,
        'max_rating' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat Response Time
    |--------------------------------------------------------------------------
    |
    | A reply inside the fast window scores 1.0; anything slower decays to
    | 0.0 at the slow window.
    |
    */

    'response_time_hours' => [
        'fast' => 2,
        'slow' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery Window
    |--------------------------------------------------------------------------
    |
    | How many days a seller has between shipping and delivery before the
    | order counts as late.
    |
    */

    'delivery_window_days' => 5,

    /*
    |--------------------------------------------------------------------------
    | Rising Bees
    |--------------------------------------------------------------------------
    |
    | A Rising Bee is a young business that has already cleared the minimum
    | order threshold. The age limit stops established sellers from appearing
    | in a "new and rising" list.
    |
    */

    'rising_bee_months' => 6,

    /*
    |--------------------------------------------------------------------------
    | Score Scale
    |--------------------------------------------------------------------------
    |
    | The published score runs from 0 to 100 rather than 0 to 1.
    |
    */

    'score_scale' => 100,

];
