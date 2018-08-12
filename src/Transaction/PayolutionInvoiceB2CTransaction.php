<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Wirecard\PaymentSdk\Transaction;

/**
 * Description of PayolutionInvoiceB2CTransaction
 *
 * @author Omar Issa
 */
class PayolutionInvoiceB2CTransaction extends PayPalTransaction implements Reservable {
    const NAME = 'payoloution-inv';
    const PAYMENT_METHOD = 'payoloution-inv';

    /**
     * @return string
     */
    public function getConfigKey()
    {
        return self::PAYMENT_METHOD;
    }
}
