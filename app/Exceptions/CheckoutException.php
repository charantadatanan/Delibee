<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A checkout problem that is safe to show to the customer (empty cart,
 * insufficient stock, product withdrawn). Internal errors deliberately do
 * not use this type, so their messages never reach the UI.
 */
class CheckoutException extends RuntimeException {}
