<?php
namespace Wirecard\PaymentSdk;


/**
 * Class Framework
 *
 * Framework class for basic structure
 *
 * @package Wirecard\PaymentSdk
 */
class Framework
{
    /**
     * Return the given world string
     *
     * @param $world
     * @return string
     */
    public function hello($world)
    {
        return 'Hello ' . $world;
    }
}
