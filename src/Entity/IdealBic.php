<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of SDK within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Entity;

use MyCLabs\Enum\Enum;

class IdealBic extends Enum
{
    const ABNANL2A = 'ABN Amro Bank';
    const ASNBNL21 = 'ASN Bank';
    const BUNQNL2A = 'bunq';
    const INGBNL2A = 'ING';
    const KNABNL2H = 'Knab';
    const RABONL2U = 'Rabobank';
    const RGGINL21 = 'Regio Bank';
    const SNSBNL2A = 'SNS Bank';
    const TRIONL2U = 'Triodos Bank';
    const FVLBNL22 = 'Van Lanschot Bankiers';
}
