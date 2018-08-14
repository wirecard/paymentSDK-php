<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Wirecard\PaymentSdk\Transaction;
use Wirecard\PaymentSdk\Transaction\PayolutionTransaction;
/**
 * Description of PayolutionInvoiceB2CTransaction
 *
 * @author Omar Issa
 */
class PayolutionInvoiceB2CTransaction extends PayolutionTransaction implements Reservable {
    const NAME = 'payolution-b2c';
    const PAYMENT_METHOD = 'payolution-b2c';

    /**
     * @return string
     */
    public function getConfigKey()
    {
        return self::PAYMENT_METHOD;
    }
}
