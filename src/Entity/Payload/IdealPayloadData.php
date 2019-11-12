<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity\Payload;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Constant\PayloadFields;
use Wirecard\PaymentSdk\Exception\MalformedPayloadException;
use Wirecard\PaymentSdk\Mapper\Response\MapperInterface;
use Wirecard\PaymentSdk\Mapper\Response\WithoutSignatureMapper;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Class IdealPayloadData
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
class IdealPayloadData implements PayloadDataInterface
{
    /**
     * @var array
     */
    private $payload;

    /**
     * @var Config
     */
    private $config;

    /**
     * IdealPayloadData constructor.
     * @param array $payload
     * @param Config $config
     * @throws \Http\Client\Exception
     * @since 4.0.0
     */
    public function __construct(array $payload, Config $config)
    {
        if (!(array_key_exists(PayloadFields::FIELD_EC, $payload) &&
            array_key_exists(PayloadFields::FIELD_TRXID, $payload) &&
            array_key_exists(PayloadFields::FIELD_REQUEST_ID, $payload)) ||
            !($payload[PayloadFields::FIELD_EC] &&
            $payload[PayloadFields::FIELD_TRXID] &&
            $payload[PayloadFields::FIELD_REQUEST_ID]
        )) {
            throw new MalformedPayloadException(
                'One of the '. PayloadFields::FIELD_EC .', '
                . PayloadFields::FIELD_TRXID . ', ' . PayloadFields::FIELD_REQUEST_ID
                . ' is missing in payload'
            );
        }

        $transactionService = $this->getTransactionService($config);
        $this->payload = $transactionService->getTransactionByRequestId(
            $payload[PayloadFields::FIELD_REQUEST_ID],
            IdealTransaction::NAME,
            false
        );

        $this->config = $config;
    }

    /**
     * @return MapperInterface
     * @since 4.0.0
     */
    public function getResponseMapper()
    {
        return new WithoutSignatureMapper($this->payload, $this->config);
    }

    /**
     * @param $config
     * @return TransactionService
     * @since 4.0.0
     */
    protected function getTransactionService($config)
    {
        return new TransactionService($config);
    }
}
