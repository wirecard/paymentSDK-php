<?php

namespace Wirecard\PaymentSdk;

class RequestIdGenerator
{
    /**
     * @param int $length The length of the generated ID.
     * @return string A random, unique ID for the request.
     */
    public function generate($length = 32)
    {
        return substr(bin2hex(openssl_random_pseudo_bytes($length)), 0, $length);
    }
}
