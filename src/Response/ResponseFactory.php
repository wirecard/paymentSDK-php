<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Response;

/**
 * Class ResponseFactory
 * @package Wirecard\PaymentSdk\Response
 * @since 4.0.0
 */
class ResponseFactory
{
    /**
     * @var ResponseDataInterface
     */
    private $responseData;

    /**
     * ResponseFactory constructor.
     * @param ResponseDataInterface $responseData
     * @since 4.0.0
     */
    public function __construct(ResponseDataInterface $responseData)
    {
        $this->responseData = $responseData;
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     * @throws \InvalidArgumentException+
     * @since 4.0.0
     */
    public function create()
    {
        switch ($this->responseData->getResponseType()) {
            case ResponseDataInterface::FORM_INTERACTION:
                $formInteractionResponse = new FormInteractionResponse(
                    $this->responseData->getData(),
                    $this->responseData->getUrl()
                );
                $formInteractionResponse->setFormFields($this->responseData->getFormFields());
                return $formInteractionResponse;
                break;
            case ResponseDataInterface::SUCCESS:
                return new SuccessResponse($this->responseData->getData());
                break;
            case ResponseDataInterface::FAILURE:
                return new FailureResponse($this->responseData->getData());
                break;
            default:
                throw new \InvalidArgumentException(
                    'A response object was unable to be created from the provided response data.'
                );
                break;
        }
    }
}
