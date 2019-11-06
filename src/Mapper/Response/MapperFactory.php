<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Mapper\Response;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\TransactionService;

/**
 * @TODO potential refactoring to abstract the data so no logic is here only creation of the mappers.
 *
 * Class MapperFactory
 * @package Wirecard\PaymentSdk\Mapper
 * @since 4.0.0
 */
class MapperFactory
{
    const FIELD_EC = 'ec';
    const FIELD_TRXID = 'trxid';
    const FIELD_REQUEST_ID = 'request_id';
    const FIELD_EPP_RESPONSE = 'eppresponse';
    const FIELD_BASE64_PAYLOAD = 'base64payload';
    const FIELD_PSP_NAME = 'psp_name';
    const FIELD_SYNC_RESPONSE = 'sync_response';
    const FIELD_RESPONSE_SIGNATURE = 'response_signature_v2';

    /**
     * @var array
     */
    private $payload;

    /**
     * @var Config
     */
    private $config;

    /**
     * ResponseMapperFactory constructor.
     */
    public function __construct(array $payload, Config $config)
    {
        $this->payload = $payload;
        $this->config = $config;
    }

    /**
     * @return MapperInterface
     * @throws \Http\Client\Exception
     * @since 4.0.0
     */
    public function create()
    {
        switch (true) {
            case $this->isNvpResponse():
                return new SeamlessMapper($this->payload);
                break;
            case $this->isPayPalResponse():
                return new WithSignatureMapper($this->payload[self::FIELD_EPP_RESPONSE], $this->config);
                break;
            case $this->isRatepayResponse():
                return new WithSignatureMapper($this->payload[self::FIELD_BASE64_PAYLOAD], $this->config);
                break;
            case $this->isSyncResponse():
                return new WithSignatureMapper($this->payload[self::FIELD_SYNC_RESPONSE], $this->config);
                break;
            case $this->isIdealResponse():
                $transactionService = new TransactionService($this->config);
                $payload = $transactionService->getTransactionByRequestId(
                    $this->payload[self::FIELD_REQUEST_ID],
                    IdealTransaction::NAME,
                    false
                );
                return new WithoutSignatureMapper($payload, $this->config);
                break;

            default:
                throw new MalformedResponseException('Missing response in payload.');
                break;
        }
    }

    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isIdealResponse()
    {
        return array_key_exists(self::FIELD_EC, $this->payload) &&
            array_key_exists(self::FIELD_TRXID, $this->payload) &&
            array_key_exists(self::FIELD_REQUEST_ID, $this->payload);
    }
    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isPayPalResponse()
    {
        return array_key_exists(self::FIELD_EPP_RESPONSE, $this->payload);
    }
    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isRatepayResponse()
    {
        return array_key_exists(self::FIELD_BASE64_PAYLOAD, $this->payload) &&
            array_key_exists(self::FIELD_PSP_NAME, $this->payload);
    }
    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isSyncResponse()
    {
        return array_key_exists(self::FIELD_SYNC_RESPONSE, $this->payload);
    }
    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isNvpResponse()
    {
        return array_key_exists(self::FIELD_RESPONSE_SIGNATURE, $this->payload);
    }
}
