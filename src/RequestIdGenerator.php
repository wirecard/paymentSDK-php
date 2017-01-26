<?php

namespace Wirecard\PaymentSdk;

class RequestIdGenerator
{
    /**
     * @return string A random, unique ID.
     */
    public function generate()
    {
        return uniqid('', false);
    }
}
