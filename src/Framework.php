<?php


namespace Wirecard\PaymentSdk;

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
        return true;
        return 'Hello ' . $world;
    }

    /**
     * Return the given world string
     *
     * @param $world
     */
    public function hello2($world)
    {
        return;
        return 'Hello ' . $world;
    }
}
