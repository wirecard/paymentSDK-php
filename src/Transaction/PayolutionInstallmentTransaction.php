<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Transaction\PayolutionTransaction;
/**
 * Description of PayolutionInstallmentTransaction
 *
 * @author Omar Issa
 */
class PayolutionInstallmentTransaction extends PayolutionTransaction implements Reservable {
    
   const NAME = 'payolution-inst';
    const PAYMENT_METHOD = 'payolution-inst';

    /**
     * @return string
     */
    public function getConfigKey()
    {
        return self::PAYMENT_METHOD;
    }
}
