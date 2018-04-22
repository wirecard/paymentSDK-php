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
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Mapper;

use PHPUnit_Framework_MockObject_MockObject;
use SimpleXMLElement;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Mapper\ResponseMapper;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\PendingResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;

/**
 * Class ResponseMapperUTest
 * @package WirecardTest\PaymentSdk\Mapper
 * @method getPaymentMethod
 */
class ResponseMapperUTest extends \PHPUnit_Framework_TestCase
{
    const STATUSES = 'statuses';
    const STATUS = 'status';
    const STATUS_CODE = 'code';
    const STATUS_DESCRIPTION = 'description';
    const STATUS_SEVERITY = 'severity';

    const PAYMENT = 'payment';
    const PAYMENT_METHODS = 'payment-methods';
    const PAYMENT_METHOD = 'payment-method';
    const PAYMENT_METHOD_NAME = 'name';
    const PAYMENT_METHOD_URL = 'url';

    const TRANSACTION_STATE = 'transaction-state';
    const TRANSACTION_ID = 'transaction-id';

    const ATTRIBUTES = '@attributes';

    /**
     * @var ResponseMapper
     */
    private $mapper;

    /**
     * @var array
     */
    private $defaultResponseArray;

    /**
     * @var Config
     */
    private $config;

    public function setUp()
    {
        $this->config = $this->createMock(Config::class);
        $this->mapper = new ResponseMapper($this->config);
        $this->defaultResponseArray = array(
            'transaction-state' => 'success',
            'transaction-type' => 'debit',
            'transaction-id' => '12345',
            'request-id' => '123',
            'statuses' => [
                ['code' => '200', 'description' => 'UnitTest', 'severity' => 'information'],
            ],
        );
    }

    /**
     * @param $content
     * @param bool $asXML
     * @return mixed|SimpleXMLElement
     */
    private function getResponse($content, $asXML = true)
    {
        $simpleOutput = new SimpleXMLElement('<?xml version="1.0"?><payment></payment>');

        foreach ($content as $contentKey => $contentValue) {
            if (!is_array($contentValue)) {
                $simpleOutput->addChild($contentKey, $contentValue);
            }
        }

        if ($content['statuses'] !== null) {
            $simpleStatuses = $simpleOutput->addChild('statuses');
            foreach ($content['statuses'] as $statuses) {
                $simpleStatus = $simpleStatuses->addChild('status');
                foreach ($statuses as $key => $value) {
                    $simpleStatus->addAttribute($key, $value);
                }
            }
        }

        if (isset($content['payment-method'])) {
            $simplePaymentMethod = $simpleOutput->addChild('payment-methods')->addChild('payment-method');
            if (is_array($content['payment-method'])) {
                foreach ($content['payment-method'] as $key => $value) {
                    $simplePaymentMethod->addAttribute($key, $value);
                }
            }
        }

        if (isset($content['three-d'])) {
            $simpleThreeD = $simpleOutput->addChild('three-d');
            foreach ($content['three-d'] as $threeDKey => $threeDValue) {
                $simpleThreeD->addChild($threeDKey, $threeDValue);
            }
        }

        if ($asXML === true) {
            return $simpleOutput->asXML();
        }
        return $simpleOutput;
    }


    public function testTransactionStateFailedReturnsFailureResponseObject()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['transaction-state'] = 'failed';
        $responseArray['statuses'] = array(
            ['code' => '200', 'description' => 'UnitTest', 'severity' => 'warning'],
            ['code' => '200', 'description' => 'UnitTest Error', 'severity' => 'error']
        );
        $mapped = $this->mapper->map($this->getResponse($responseArray));

        $this->assertInstanceOf(FailureResponse::class, $mapped);
        /**
         * @var FailureResponse $mapped
         */
        $this->assertCount(2, $mapped->getStatusCollection());
    }

    public function testTransactionStateSuccessReturnsFilledInteractionResponseObject()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['payment-method'] = array('name' => 'paypal', 'url' => 'http://www.example.com/redirect-url');
        $response = $this->getResponse($responseArray);
        /**
         * @var $mapped InteractionResponse
         */
        $mapped = $this->mapper->map($response, new PayPalTransaction());
        $this->assertInstanceOf(InteractionResponse::class, $mapped);
        /**
         * @var InteractionResponse $mapped
         */
        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('http://www.example.com/redirect-url', $mapped->getRedirectUrl());
        $this->assertCount(1, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNon3DCheckEnrollmentThrowsError()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['transaction-type'] = 'check-enrollment';
        $responseArray['payment-method'] = array('name' => 'paypal', 'url' => 'http://www.example.com/redirect-url');
        $response = $this->getResponse($responseArray);

        $this->mapper->map($response, new PayPalTransaction());
    }

    public function testCardTokenReturnsPaymentMethodCreditCard()
    {
        $helper = function () {
            $this->simpleXml = new \SimpleXMLElement('<xml><card-token>123</card-token></xml>');
            return $this->getPaymentMethod();
        };
        $method = $helper->bindTo($this->mapper, $this->mapper);

        $expected = new \SimpleXMLElement('<payment-methods>
                                              <payment-method name="creditcard"></payment-method>
                                          </payment-methods>');

        $this->assertEquals($expected, $method());
    }

    public function testTransactionStateSuccessReturnsFilledSuccessResponseObject()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['payment-method'] = array('name' => 'paypal');
        $responseArray['statuses'][0]['provider-transaction-id'] = "W0RWI653B31MAU649";
        $response = $this->getResponse($responseArray);

        $mapped = $this->mapper->map($response, new PayPalTransaction());
        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        /**
         * @var SuccessResponse $mapped
         */
        $this->assertEquals('W0RWI653B31MAU649', $mapped->getProviderTransactionId());
        $this->assertCount(1, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    public function testBase64encodedTransactionStateSuccessReturnsFilledSuccessResponseObject()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['payment-method'] = array('name' => 'paypal');
        $response = base64_encode($this->getResponse($responseArray));

        $mapped = $this->mapper->map($response, new PayPalTransaction());
        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        /**
         * @var SuccessResponse $mapped
         */
        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertCount(1, $mapped->getStatusCollection());
        $this->assertEquals(base64_decode($response), $mapped->getRawData());
    }

    public function testWithValidResponseThreeDTransactionReturnsFormInteractionResponse()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['transaction-type'] = 'check-enrollment';
        $responseArray['three-d'] = array('acs-url' => 'https://www.example.com/acs', 'pareq' => 'request');
        $payload = $this->getResponse($responseArray);

        $transaction = new CreditCardTransaction();
        /**
         * @var FormInteractionResponse $mapped
         */
        $mapped = $this->mapper->map($payload, $transaction);

        $this->assertInstanceOf(FormInteractionResponse::class, $mapped);
        $this->assertEquals($payload, $mapped->getRawData());
    }

    public function testWithValidResponseThreeDTransactionReturnsFormInteractionResponseWithMd()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['transaction-type'] = 'check-enrollment';
        $responseArray['three-d'] = array('acs-url' => 'https://www.example.com/acs', 'pareq' => 'request');
        $payload = $this->getResponse($responseArray);

        $transaction = new CreditCardTransaction();
        $transaction->setOperation(Operation::RESERVE);

        /**
         * @var FormInteractionResponse $mapped
         */
        $mapped = $this->mapper->map($payload, $transaction);

        $this->assertInstanceOf(FormInteractionResponse::class, $mapped);
        $this->assertEquals($payload, $mapped->getRawData());
        $this->assertEquals(
            '{"enrollment-check-transaction-id":"12345","operation-type":"authorization"}',
            base64_decode($mapped->getFormFields()->getIterator()['MD'])
        );
    }

    public function testWithValidResponseThreeDTransactionReturnsFormInteractionResponseWithTermUrl()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['transaction-type'] = 'check-enrollment';
        $responseArray['three-d'] = array('acs-url' => 'https://www.example.com/acs', 'pareq' => 'request');
        $payload = $this->getResponse($responseArray);

        $transaction = new CreditCardTransaction();
        $transaction->setTermUrl('dummy URL');

        /**
         * @var FormInteractionResponse $mapped
         */
        $mapped = $this->mapper->map($payload, $transaction);

        $this->assertInstanceOf(FormInteractionResponse::class, $mapped);
        $this->assertEquals('dummy URL', $mapped->getFormFields()->getIterator()['TermUrl']);
    }

    public function testWithValidResponsePayPalTransactionReturnsSuccessResponse()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['payment-method'] = array('name' => 'paypal');
        $payload = $this->getResponse($responseArray);

        $mapped = $this->mapper->map($payload, new PayPalTransaction());

        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        $this->assertEquals($payload, $mapped->getRawData());
    }

    public function invalidResponseProvider()
    {
        return [
            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                    </payment>')->asXML(), null],
            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                        </payment-methods>
                    </payment>')->asXML(), null],
            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                            <payment-method name="eft"></payment-method>
                        </payment-methods>
                    </payment>')->asXML(), null],
            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML(), null],

            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses></statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML(), null],

            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <transaction-id>12345</transaction-id>
                        <statuses>
                            <status 
                            code="305.0000" 
                            description="paypal:Status before." 
                            provider-transaction-id="xxx" 
                            severity="information"/>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML(), null],
            [simplexml_load_string('<payment>
                           <transaction-state>success</transaction-state>
                           <transaction-id>12345</transaction-id>
                           <transaction-type>debit</transaction-type>
                           <request-id>123</request-id>
                           <statuses>
                               <status 
                               code="305.0000" 
                               description="paypal:Status before." 
                               provider-transaction-id="xxx" 
                               severity="information"/>
                               <status 
                               code="201.0000" 
                               description="paypal:The resource was successfully created." 
                               provider-transaction-id="W0RWI653B31MAU649" 
                               severity="information"/>
                           </statuses>
                  </payment>')->asXML(), $this->createMock(CreditCardTransaction::class)],
            [simplexml_load_string('<payment>
                           <transaction-state>success</transaction-state>
                           <transaction-id>12345</transaction-id>
                           <transaction-type>debit</transaction-type>
                           <request-id>123</request-id>
                           <statuses>
                               <status 
                               code="305.0000" 
                               description="paypal:Status before." 
                               provider-transaction-id="xxx" 
                               severity="information"/>
                               <status 
                               code="201.0000" 
                               description="paypal:The resource was successfully created." 
                               provider-transaction-id="W0RWI653B31MAU649" 
                               severity="information"/>
                           </statuses>
                           <three-d></three-d>
                  </payment>')->asXML(), $this->createMock(CreditCardTransaction::class)],
            [simplexml_load_string('<payment>
                           <transaction-state>success</transaction-state>
                           <transaction-id>12345</transaction-id>
                           <transaction-type>debit</transaction-type>
                           <request-id>123</request-id>
                           <statuses>
                               <status 
                               code="305.0000" 
                               description="paypal:Status before." 
                               provider-transaction-id="xxx" 
                               severity="information"/>
                               <status 
                               code="201.0000" 
                               description="paypal:The resource was successfully created." 
                               provider-transaction-id="W0RWI653B31MAU649" 
                               severity="information"/>
                           </statuses>
                           <three-d>
                               <acs-url>https://www.example.com/acs</acs-url>
                           </three-d>
                  </payment>')->asXML(), $this->createMock(CreditCardTransaction::class)],
        ];
    }

    public function testMoreStatusesWithTheSameProviderTransactionIdReturnsSuccess()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['payment-method'] = array('name' => 'paypal');
        $responseArray['statuses'] = array(
            ['code' => '305.0000',
                'description' => 'paypal:Status before.',
                'provider-transaction-id' => 'xxx',
                'severity' => 'information'],
            ['code' => '201.0000',
                'description' => 'paypal:The resource was successfully created.',
                'provider-transaction-id' => 'xxx',
                'severity' => 'information'
            ]
        );
        $response = $this->getResponse($responseArray);

        $mapped = $this->mapper->map($response, new PayPalTransaction());
        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        /**
         * @var SuccessResponse $mapped
         */
        $this->assertCount(2, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    public function testMoreStatusesOnlyOneHasProviderTransactionIdReturnsSuccess()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['payment-method'] = array('name' => 'paypal');
        $responseArray['statuses'] = array(
            ['code' => '305.0000',
                'description' => 'paypal:Status before.',
                'severity' => 'information'],
            ['code' => '201.0000',
                'description' => 'paypal:The resource was successfully created.',
                'provider-transaction-id' => 'xxx',
                'severity' => 'information'
            ]
        );
        $response = $this->getResponse($responseArray);

        $mapped = $this->mapper->map($response, new PayPalTransaction());
        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        /**
         * @var SuccessResponse $mapped
         */
        $this->assertCount(2, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }


    public function testTransactionStateInProgressReturnsPendingResponseObject()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['transaction-state'] = 'in-progress';
        $response = $this->getResponse($responseArray);
        /**
         * @var PendingResponse $mapped
         */
        $mapped = $this->mapper->map($response, new SepaTransaction());
        $this->assertInstanceOf(PendingResponse::class, $mapped);
        $this->assertEquals('123', $mapped->getRequestId());
    }

    public function signaturePublicKeyProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            [
                true,
                file_get_contents(__DIR__ . '/../../examples/inc/api-test.wirecard.com.crt'),
                '<?xml version="1.0" encoding="UTF-8"?><payment xmlns="http://www.elastic-payments.com/schema/payment" xmlns:ns2="http://www.elastic-payments.com/schema/epa/transaction"><merchant-account-id>9abf05c1-c266-46ae-8eac-7f87ca97af28</merchant-account-id><transaction-id>ccde5d9b-db51-4377-977f-51c8f3a170c0</transaction-id><request-id>845ea3ed40b77f598a96441531395ba6</request-id><transaction-type>authorization</transaction-type><transaction-state>success</transaction-state><completion-time-stamp>2017-03-29T06:58:47.000Z</completion-time-stamp><statuses><status code="201.0000" description="The resource was successfully created." provider-transaction-id="87D646135U668492X" severity="information"/></statuses><requested-amount currency="EUR">12.590000</requested-amount><parent-transaction-id>a3bdd4e9-b0b2-4167-be62-7f09c1eb368f</parent-transaction-id><account-holder><first-name>Wirecardbuyer</first-name><last-name>Spintzyk</last-name><email>paypal.buyer2@wirecard.com</email></account-holder><shipping><first-name>Chlo</first-name><last-name>Li</last-name><address><street1>Milan</street1><city>MilAN</city><country>IT</country><postal-code>12234</postal-code></address></shipping><ip-address>127.0.0.1</ip-address><order-items><order-item><name>Item 1</name><description>My first item</description><article-number>A1</article-number><amount currency="EUR">2.590000</amount><quantity>1</quantity></order-item><order-item><name>Item 2</name><description>My second item</description><article-number>B2</article-number><amount currency="EUR">5.000000</amount><tax-amount currency="EUR">1.000000</tax-amount><quantity>2</quantity></order-item></order-items><notifications><notification url="http://localhost/PayPal/notify.php"/></notifications><payment-methods><payment-method name="paypal"/></payment-methods><api-id>---</api-id><cancel-redirect-url>http://localhost/PayPal/return.php?status=cancel</cancel-redirect-url><success-redirect-url>http://localhost/PayPal/return.php?status=success</success-redirect-url><wallet><account-id>ZNKTXUBNSQE2Y</account-id></wallet><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>J5r1OC85Cgy0zqTgW+G2IWJ6NEWPMh0uR123j+c7hEQ=</DigestValue></Reference></SignedInfo><SignatureValue>ddS79IgUfxrbI6Z/IbhltvNR60khscrVUKB1zcg3/CQv/Ow7SjrueGeM/EemoFW9pWoH5jh+q9FX5cpqeamButaxYNRjJTF/JAK5rz8zBNIrpyfHln3u3EmUMGAfHOFOlHjyg3azcXhwc8Kxg5CZsSOuho4At7h15UK34FhTClR2ae5DEmttZG6jLLbk0tlXnJTRq/OrrLETSoHB13Yyr34VNb/w7fyrKiycj6ls0RZK6OwukHFBjzqU15+CqYalvHLjeKmeEDy0z7kN5V7lQqK1iFqy0lKpdRNWME6bgkiHWz7AqBPElepaXfljH3uIC9ICSE3fZj0cFosVWqS5qujd6jviLgArPylbLzsPpmMr7uNEuGYn/JosbrGLmaEdOPx6n99QH9ZJEqhny+Hhb/yhCUOULzBTUThhryAp1K8OqVMHKr04Yr4WhO6qvWG0FRZscX9BDRlUVYv3af8rFB4vklrh40SzmX3agJ8dFwYwf1LXlc0224rzUL7IeogD1zlbtdUODvhyVIQ9eq/f0xyJ60MSo9Jo3iud+gTY7vX/Uv8OfOYycD12C5LlKAtLp1aoG9/8OO3SBwwb9V/3m9ALEajx1cgIZDe4E4AlMg65BWpiaQ4/QaRmNMH04K7xYXTp4AdCNXev0YIB2FdyID9MsKi28uJYnskxlSfwEXc=</SignatureValue></Signature></payment>'
            ],
            [
                true,
                null,
                '<?xml version="1.0" encoding="UTF-8"?><payment xmlns="http://www.elastic-payments.com/schema/payment" xmlns:ns2="http://www.elastic-payments.com/schema/epa/transaction"><merchant-account-id>9abf05c1-c266-46ae-8eac-7f87ca97af28</merchant-account-id><transaction-id>ccde5d9b-db51-4377-977f-51c8f3a170c0</transaction-id><request-id>845ea3ed40b77f598a96441531395ba6</request-id><transaction-type>authorization</transaction-type><transaction-state>success</transaction-state><completion-time-stamp>2017-03-29T06:58:47.000Z</completion-time-stamp><statuses><status code="201.0000" description="The resource was successfully created." provider-transaction-id="87D646135U668492X" severity="information"/></statuses><requested-amount currency="EUR">12.590000</requested-amount><parent-transaction-id>a3bdd4e9-b0b2-4167-be62-7f09c1eb368f</parent-transaction-id><account-holder><first-name>Wirecardbuyer</first-name><last-name>Spintzyk</last-name><email>paypal.buyer2@wirecard.com</email></account-holder><shipping><first-name>Chlo</first-name><last-name>Li</last-name><address><street1>Milan</street1><city>MilAN</city><country>IT</country><postal-code>12234</postal-code></address></shipping><ip-address>127.0.0.1</ip-address><order-items><order-item><name>Item 1</name><description>My first item</description><article-number>A1</article-number><amount currency="EUR">2.590000</amount><quantity>1</quantity></order-item><order-item><name>Item 2</name><description>My second item</description><article-number>B2</article-number><amount currency="EUR">5.000000</amount><tax-amount currency="EUR">1.000000</tax-amount><quantity>2</quantity></order-item></order-items><notifications><notification url="http://localhost/PayPal/notify.php"/></notifications><payment-methods><payment-method name="paypal"/></payment-methods><api-id>---</api-id><cancel-redirect-url>http://localhost/PayPal/return.php?status=cancel</cancel-redirect-url><success-redirect-url>http://localhost/PayPal/return.php?status=success</success-redirect-url><wallet><account-id>ZNKTXUBNSQE2Y</account-id></wallet><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>J5r1OC85Cgy0zqTgW+G2IWJ6NEWPMh0uR123j+c7hEQ=</DigestValue></Reference></SignedInfo><SignatureValue>ddS79IgUfxrbI6Z/IbhltvNR60khscrVUKB1zcg3/CQv/Ow7SjrueGeM/EemoFW9pWoH5jh+q9FX5cpqeamButaxYNRjJTF/JAK5rz8zBNIrpyfHln3u3EmUMGAfHOFOlHjyg3azcXhwc8Kxg5CZsSOuho4At7h15UK34FhTClR2ae5DEmttZG6jLLbk0tlXnJTRq/OrrLETSoHB13Yyr34VNb/w7fyrKiycj6ls0RZK6OwukHFBjzqU15+CqYalvHLjeKmeEDy0z7kN5V7lQqK1iFqy0lKpdRNWME6bgkiHWz7AqBPElepaXfljH3uIC9ICSE3fZj0cFosVWqS5qujd6jviLgArPylbLzsPpmMr7uNEuGYn/JosbrGLmaEdOPx6n99QH9ZJEqhny+Hhb/yhCUOULzBTUThhryAp1K8OqVMHKr04Yr4WhO6qvWG0FRZscX9BDRlUVYv3af8rFB4vklrh40SzmX3agJ8dFwYwf1LXlc0224rzUL7IeogD1zlbtdUODvhyVIQ9eq/f0xyJ60MSo9Jo3iud+gTY7vX/Uv8OfOYycD12C5LlKAtLp1aoG9/8OO3SBwwb9V/3m9ALEajx1cgIZDe4E4AlMg65BWpiaQ4/QaRmNMH04K7xYXTp4AdCNXev0YIB2FdyID9MsKi28uJYnskxlSfwEXc=</SignatureValue><KeyInfo><X509Data><X509SubjectName>L=Ascheim,2.5.4.4=#130642617965726e,CN=api-test.wirecard.com,OU=Operations,O=Wirecard Technologies GmbH,C=DE</X509SubjectName><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></payment>'
            ],
            // no signature provided
            [
                false,
                null,
                '<?xml version="1.0" encoding="UTF-8"?><payment xmlns="http://www.elastic-payments.com/schema/payment" xmlns:ns2="http://www.elastic-payments.com/schema/epa/transaction"><merchant-account-id>9abf05c1-c266-46ae-8eac-7f87ca97af28</merchant-account-id><transaction-id>ccde5d9b-db51-4377-977f-51c8f3a170c0</transaction-id><request-id>845ea3ed40b77f598a96441531395ba6</request-id><transaction-type>authorization</transaction-type><transaction-state>success</transaction-state><completion-time-stamp>2017-03-29T06:58:47.000Z</completion-time-stamp><statuses><status code="201.0000" description="The resource was successfully created." provider-transaction-id="87D646135U668492X" severity="information"/></statuses><requested-amount currency="EUR">12.590000</requested-amount><parent-transaction-id>a3bdd4e9-b0b2-4167-be62-7f09c1eb368f</parent-transaction-id><account-holder><first-name>Wirecardbuyer</first-name><last-name>Spintzyk</last-name><email>paypal.buyer2@wirecard.com</email></account-holder><shipping><first-name>Chlo</first-name><last-name>Li</last-name><address><street1>Milan</street1><city>MilAN</city><country>IT</country><postal-code>12234</postal-code></address></shipping><ip-address>127.0.0.1</ip-address><order-items><order-item><name>Item 1</name><description>My first item</description><article-number>A1</article-number><amount currency="EUR">2.590000</amount><quantity>1</quantity></order-item><order-item><name>Item 2</name><description>My second item</description><article-number>B2</article-number><amount currency="EUR">5.000000</amount><tax-amount currency="EUR">1.000000</tax-amount><quantity>2</quantity></order-item></order-items><notifications><notification url="http://localhost/PayPal/notify.php"/></notifications><payment-methods><payment-method name="paypal"/></payment-methods><api-id>---</api-id><cancel-redirect-url>http://localhost/PayPal/return.php?status=cancel</cancel-redirect-url><success-redirect-url>http://localhost/PayPal/return.php?status=success</success-redirect-url><wallet><account-id>ZNKTXUBNSQE2Y</account-id></wallet></payment>'
            ],
            [
                false,
                null,
                '<payment><transaction-state>in-progress</transaction-state><transaction-type>debit</transaction-type><request-id>1234</request-id><statuses><status code="1" description="a" severity="0"></status></statuses><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms></Reference></SignedInfo><SignatureValue>ddS79IgUfxrbI6Z/IbhltvNR60khscrVUKB1zcg3/CQv/Ow7SjrueGeM/EemoFW9pWoH5jh+q9FX5cpqeamButaxYNRjJTF/JAK5rz8zBNIrpyfHln3u3EmUMGAfHOFOlHjyg3azcXhwc8Kxg5CZsSOuho4At7h15UK34FhTClR2ae5DEmttZG6jLLbk0tlXnJTRq/OrrLETSoHB13Yyr34VNb/w7fyrKiycj6ls0RZK6OwukHFBjzqU15+CqYalvHLjeKmeEDy0z7kN5V7lQqK1iFqy0lKpdRNWME6bgkiHWz7AqBPElepaXfljH3uIC9ICSE3fZj0cFosVWqS5qujd6jviLgArPylbLzsPpmMr7uNEuGYn/JosbrGLmaEdOPx6n99QH9ZJEqhny+Hhb/yhCUOULzBTUThhryAp1K8OqVMHKr04Yr4WhO6qvWG0FRZscX9BDRlUVYv3af8rFB4vklrh40SzmX3agJ8dFwYwf1LXlc0224rzUL7IeogD1zlbtdUODvhyVIQ9eq/f0xyJ60MSo9Jo3iud+gTY7vX/Uv8OfOYycD12C5LlKAtLp1aoG9/8OO3SBwwb9V/3m9ALEajx1cgIZDe4E4AlMg65BWpiaQ4/QaRmNMH04K7xYXTp4AdCNXev0YIB2FdyID9MsKi28uJYnskxlSfwEXc=</SignatureValue><KeyInfo><X509Data><X509SubjectName>L=Ascheim,2.5.4.4=#130642617965726e,CN=api-test.wirecard.com,OU=Operations,O=Wirecard Technologies GmbH,C=DE</X509SubjectName><X509Certificate>MIIF5DCCBMygAwIBAgICLHQwDQYJKoZIhvcNAQELBQAwWzELMAkGA1UEBhMCREUxETAPBgNVBAoTCFdpcmVjYXJkMTkwNwYDVQQDFDB3aXJlY2FyZC1EUS1NVUMtaW50ZXJuYWwtd2Vic2VydmljZS1pc3N1aW5nQ0FfMDIwHhcNMTcwMTEyMTM1OTI2WhcNMTkwMTEyMTM1OTI2WjCBijELMAkGA1UEBhMCREUxIzAhBgNVBAoTGldpcmVjYXJkIFRlY2hub2xvZ2llcyBHbWJIMRMwEQYDVQQLEwpPcGVyYXRpb25zMR4wHAYDVQQDExVhcGktdGVzdC53aXJlY2FyZC5jb20xDzANBgNVBAQTBkJheWVybjEQMA4GA1UEBxMHQXNjaGVpbTCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBAKSkExBY8FjRcZdrxOuJF+HZY8+McQaOB8B0E/hTUhoclsF4OJNaMThje7R6w6OYWBMKpssGngHFaZv35rCo5XVUpJmjZa04ytxE72GKO/uP4yIR7ZBXZx42B22MFaJJZTgPRCCFd6jrz906BZ//CmEAmk5gKelfPxfWJgGyTX6xz7I9R/G57E1xNOuEihN0ma5Q2IhD71MPVseFIGazyfGbJD6rYYbeBbOQSGk//TL8sdRCn0BLcm4DH5oqcPxDKzkaBP4ohNkCWsxpLLSyV6Wx0ihT0S1OLVNkEeTvcrYgUk124VyGatwWNUuCBYyOGQSOGqrW8IHmrhjzzT0NQog0/m38lpdqw/eWmt39qhODqSfILUk2Dxv1+W0IRKJCKcJrcTbXEQCuHl+XWY+U2AhinIPNRA0KX2oOgC//inwyKWSGWHdQnaake646R1wHqtoEfCtEcfyaeR+IrMr1rCAA3RZ+MH1J5UlUCWcnxPT0kad6dUwe3Qjq3jK4gaFzYU2yVScX5LVZMlWy2NiGCIvngHQmhArESzxMVvz5METZujfax6hfmiLNRWu0Zqs09Mpxy5zk5m/WRi5izb0uBeCfcA6x9pmjMx8M4OGG5RO2HTXSwLYJTKI47VXNsLLOY+nMFmhj/dkLJ5d3zI7EczToPMRHmHG7EqEdAfbb+oUlAgMBAAGjggGAMIIBfDARBgNVHQ4ECgQIS6wVIA0mJ9IwEwYDVR0jBAwwCoAIQ2weFtQ9BQ4wCwYDVR0PBAQDAgTwMIIBQwYDVR0fBIIBOjCCATYwggEyoIIBLqCCASqGgdVsZGFwOi8vd2lyZWNhcmQubGFuL0NOPXdpcmVjYXJkLURRLU1VQy1pbnRlcm5hbC13ZWJzZXJ2aWNlLWlzc3VpbmdDQV8wMixDTj1DRFAsQ049UHVibGljIEtleSBTZXJ2aWNlcyxDTj1TZXJ2aWNlcyxDTj1Db25maWd1cmF0aW9uLGRjPXdpcmVjYXJkLGRjPWxhbj9jZXJ0aWZpY2F0ZVJldm9jYXRpb25MaXN0P2Jhc2U/b2JqZWN0Q2xhc3M9Q1JMRGlzdHJpYnV0aW9uUG9pbnSGUGh0dHA6Ly9jcmwud2lyZWNhcmQubGFuL0NSTF93aXJlY2FyZC1EUS1NVUMtaW50ZXJuYWwtd2Vic2VydmljZS1pc3N1aW5nQ0FfMDIuY3JsMA0GCSqGSIb3DQEBCwUAA4IBAQAmlUoiEFPRsOjGPb7SYiuJLxqTXCvZQeuXiUydF6FQl/zIpR/zSltaZKK86L+1i7t1C89OyTTXBD9FN6EKmlHo/ulsMn9V2B4zK3lT/NUclST98BmCla4Jzm+roeOHTqlPz3gPRJiPsr3wdvM+FSAJ2MRdv3l77mTE3v3hjsVVMmShR3VwwpxCICl3mpMsSaJZLyJdOHwvnpXs1m9kESwPD3DQ3RAQ/OGa0pPxAkHaauog4DhPvr/nBQnWHd2Us5b/ep7LME9hZ8u3hu/Kc6Vk24c5p3WUOiyaTiw+Ym3QDXl1wBSl9DdM94KbmAAQ5D/FUqyQnSc4TpmYvJ+Iavag</X509Certificate></X509Data></KeyInfo></Signature></payment>'
            ],
            [
                false,
                null,
                '<payment><transaction-state>in-progress</transaction-state><transaction-type>debit</transaction-type><request-id>1234</request-id><statuses><status code="1" description="a" severity="0"></status></statuses><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>J5r1OC85Cgy0zqTgW+G2IWJ6NEWPMh0uR123j+c7hEQ=</DigestValue></Reference></SignedInfo><SignatureValue>ddS79IgUfxrbI6Z/IbhltvNR60khscrVUKB1zcg3/CQv/Ow7SjrueGeM/EemoFW9pWoH5jh+q9FX5cpqeamButaxYNRjJTF/JAK5rz8zBNIrpyfHln3u3EmUMGAfHOFOlHjyg3azcXhwc8Kxg5CZsSOuho4At7h15UK34FhTClR2ae5DEmttZG6jLLbk0tlXnJTRq/OrrLETSoHB13Yyr34VNb/w7fyrKiycj6ls0RZK6OwukHFBjzqU15+CqYalvHLjeKmeEDy0z7kN5V7lQqK1iFqy0lKpdRNWME6bgkiHWz7AqBPElepaXfljH3uIC9ICSE3fZj0cFosVWqS5qujd6jviLgArPylbLzsPpmMr7uNEuGYn/JosbrGLmaEdOPx6n99QH9ZJEqhny+Hhb/yhCUOULzBTUThhryAp1K8OqVMHKr04Yr4WhO6qvWG0FRZscX9BDRlUVYv3af8rFB4vklrh40SzmX3agJ8dFwYwf1LXlc0224rzUL7IeogD1zlbtdUODvhyVIQ9eq/f0xyJ60MSo9Jo3iud+gTY7vX/Uv8OfOYycD12C5LlKAtLp1aoG9/8OO3SBwwb9V/3m9ALEajx1cgIZDe4E4AlMg65BWpiaQ4/QaRmNMH04K7xYXTp4AdCNXev0YIB2FdyID9MsKi28uJYnskxlSfwEXc=</SignatureValue><KeyInfo><X509Data><X509SubjectName>L=Ascheim,2.5.4.4=#130642617965726e,CN=api-test.wirecard.com,OU=Operations,O=Wirecard Technologies GmbH,C=DE</X509SubjectName><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></payment>'
            ],
            [
                false,
                'test',
                '<?xml version="1.0" encoding="UTF-8"?><payment xmlns="http://www.elastic-payments.com/schema/payment" xmlns:ns2="http://www.elastic-payments.com/schema/epa/transaction"><merchant-account-id>9abf05c1-c266-46ae-8eac-7f87ca97af28</merchant-account-id><transaction-id>ccde5d9b-db51-4377-977f-51c8f3a170c0</transaction-id><request-id>845ea3ed40b77f598a96441531395ba6</request-id><transaction-type>authorization</transaction-type><transaction-state>success</transaction-state><completion-time-stamp>2017-03-29T06:58:47.000Z</completion-time-stamp><statuses><status code="201.0000" description="The resource was successfully created." provider-transaction-id="87D646135U668492X" severity="information"/></statuses><requested-amount currency="EUR">12.590000</requested-amount><parent-transaction-id>a3bdd4e9-b0b2-4167-be62-7f09c1eb368f</parent-transaction-id><account-holder><first-name>Wirecardbuyer</first-name><last-name>Spintzyk</last-name><email>paypal.buyer2@wirecard.com</email></account-holder><shipping><first-name>Chlo</first-name><last-name>Li</last-name><address><street1>Milan</street1><city>MilAN</city><country>IT</country><postal-code>12234</postal-code></address></shipping><ip-address>127.0.0.1</ip-address><order-items><order-item><name>Item 1</name><description>My first item</description><article-number>A1</article-number><amount currency="EUR">2.590000</amount><quantity>1</quantity></order-item><order-item><name>Item 2</name><description>My second item</description><article-number>B2</article-number><amount currency="EUR">5.000000</amount><tax-amount currency="EUR">1.000000</tax-amount><quantity>2</quantity></order-item></order-items><notifications><notification url="http://localhost/PayPal/notify.php"/></notifications><payment-methods><payment-method name="paypal"/></payment-methods><api-id>---</api-id><cancel-redirect-url>http://localhost/PayPal/return.php?status=cancel</cancel-redirect-url><success-redirect-url>http://localhost/PayPal/return.php?status=success</success-redirect-url><wallet><account-id>ZNKTXUBNSQE2Y</account-id></wallet><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>J5r1OC85Cgy0zqTgW+G2IWJ6NEWPMh0uR123j+c7hEQ=</DigestValue></Reference></SignedInfo><SignatureValue>ddS79IgUfxrbI6Z/IbhltvNR60khscrVUKB1zcg3/CQv/Ow7SjrueGeM/EemoFW9pWoH5jh+q9FX5cpqeamButaxYNRjJTF/JAK5rz8zBNIrpyfHln3u3EmUMGAfHOFOlHjyg3azcXhwc8Kxg5CZsSOuho4At7h15UK34FhTClR2ae5DEmttZG6jLLbk0tlXnJTRq/OrrLETSoHB13Yyr34VNb/w7fyrKiycj6ls0RZK6OwukHFBjzqU15+CqYalvHLjeKmeEDy0z7kN5V7lQqK1iFqy0lKpdRNWME6bgkiHWz7AqBPElepaXfljH3uIC9ICSE3fZj0cFosVWqS5qujd6jviLgArPylbLzsPpmMr7uNEuGYn/JosbrGLmaEdOPx6n99QH9ZJEqhny+Hhb/yhCUOULzBTUThhryAp1K8OqVMHKr04Yr4WhO6qvWG0FRZscX9BDRlUVYv3af8rFB4vklrh40SzmX3agJ8dFwYwf1LXlc0224rzUL7IeogD1zlbtdUODvhyVIQ9eq/f0xyJ60MSo9Jo3iud+gTY7vX/Uv8OfOYycD12C5LlKAtLp1aoG9/8OO3SBwwb9V/3m9ALEajx1cgIZDe4E4AlMg65BWpiaQ4/QaRmNMH04K7xYXTp4AdCNXev0YIB2FdyID9MsKi28uJYnskxlSfwEXc=</SignatureValue><KeyInfo><X509Data><X509SubjectName>L=Ascheim,2.5.4.4=#130642617965726e,CN=api-test.wirecard.com,OU=Operations,O=Wirecard Technologies GmbH,C=DE</X509SubjectName><X509Certificate>MIIF5DCCBMygAwIBAgICLHQwDQYJKoZIhvcNAQELBQAwWzELMAkGA1UEBhMCREUxETAPBgNVBAoTCFdpcmVjYXJkMTkwNwYDVQQDFDB3aXJlY2FyZC1EUS1NVUMtaW50ZXJuYWwtd2Vic2VydmljZS1pc3N1aW5nQ0FfMDIwHhcNMTcwMTEyMTM1OTI2WhcNMTkwMTEyMTM1OTI2WjCBijELMAkGA1UEBhMCREUxIzAhBgNVBAoTGldpcmVjYXJkIFRlY2hub2xvZ2llcyBHbWJIMRMwEQYDVQQLEwpPcGVyYXRpb25zMR4wHAYDVQQDExVhcGktdGVzdC53aXJlY2FyZC5jb20xDzANBgNVBAQTBkJheWVybjEQMA4GA1UEBxMHQXNjaGVpbTCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBAKSkExBY8FjRcZdrxOuJF+HZY8+McQaOB8B0E/hTUhoclsF4OJNaMThje7R6w6OYWBMKpssGngHFaZv35rCo5XVUpJmjZa04ytxE72GKO/uP4yIR7ZBXZx42B22MFaJJZTgPRCCFd6jrz906BZ//CmEAmk5gKelfPxfWJgGyTX6xz7I9R/G57E1xNOuEihN0ma5Q2IhD71MPVseFIGazyfGbJD6rYYbeBbOQSGk//TL8sdRCn0BLcm4DH5oqcPxDKzkaBP4ohNkCWsxpLLSyV6Wx0ihT0S1OLVNkEeTvcrYgUk124VyGatwWNUuCBYyOGQSOGqrW8IHmrhjzzT0NQog0/m38lpdqw/eWmt39qhODqSfILUk2Dxv1+W0IRKJCKcJrcTbXEQCuHl+XWY+U2AhinIPNRA0KX2oOgC//inwyKWSGWHdQnaake646R1wHqtoEfCtEcfyaeR+IrMr1rCAA3RZ+MH1J5UlUCWcnxPT0kad6dUwe3Qjq3jK4gaFzYU2yVScX5LVZMlWy2NiGCIvngHQmhArESzxMVvz5METZujfax6hfmiLNRWu0Zqs09Mpxy5zk5m/WRi5izb0uBeCfcA6x9pmjMx8M4OGG5RO2HTXSwLYJTKI47VXNsLLOY+nMFmhj/dkLJ5d3zI7EczToPMRHmHG7EqEdAfbb+oUlAgMBAAGjggGAMIIBfDARBgNVHQ4ECgQIS6wVIA0mJ9IwEwYDVR0jBAwwCoAIQ2weFtQ9BQ4wCwYDVR0PBAQDAgTwMIIBQwYDVR0fBIIBOjCCATYwggEyoIIBLqCCASqGgdVsZGFwOi8vd2lyZWNhcmQubGFuL0NOPXdpcmVjYXJkLURRLU1VQy1pbnRlcm5hbC13ZWJzZXJ2aWNlLWlzc3VpbmdDQV8wMixDTj1DRFAsQ049UHVibGljIEtleSBTZXJ2aWNlcyxDTj1TZXJ2aWNlcyxDTj1Db25maWd1cmF0aW9uLGRjPXdpcmVjYXJkLGRjPWxhbj9jZXJ0aWZpY2F0ZVJldm9jYXRpb25MaXN0P2Jhc2U/b2JqZWN0Q2xhc3M9Q1JMRGlzdHJpYnV0aW9uUG9pbnSGUGh0dHA6Ly9jcmwud2lyZWNhcmQubGFuL0NSTF93aXJlY2FyZC1EUS1NVUMtaW50ZXJuYWwtd2Vic2VydmljZS1pc3N1aW5nQ0FfMDIuY3JsMA0GCSqGSIb3DQEBCwUAA4IBAQAmlUoiEFPRsOjGPb7SYiuJLxqTXCvZQeuXiUydF6FQl/zIpR/zSltaZKK86L+1i7t1C89OyTTXBD9FN6EKmlHo/ulsMn9V2B4zK3lT/NUclST98BmCla4Jzm+roeOHTqlPz3gPRJiPsr3wdvM+FSAJ2MRdv3l77mTE3v3hjsVVMmShR3VwwpxCICl3mpMsSaJZLyJdOHwvnpXs1m9kESwPD3DQ3RAQ/OGa0pPxAkHaauog4DhPvr/nBQnWHd2Us5b/ep7LME9hZ8u3hu/Kc6Vk24c5p3WUOiyaTiw+Ym3QDXl1wBSl9DdM94KbmAAQ5D/FUqyQnSc4TpmYvJ+Iavag</X509Certificate></X509Data></KeyInfo></Signature></payment>'
            ],
            [
                false,
                file_get_contents(__DIR__ . '/../resource/invalid.crt'),
                '<?xml version="1.0" encoding="UTF-8"?><payment xmlns="http://www.elastic-payments.com/schema/payment" xmlns:ns2="http://www.elastic-payments.com/schema/epa/transaction"><merchant-account-id>9abf05c1-c266-46ae-8eac-7f87ca97af28</merchant-account-id><transaction-id>ccde5d9b-db51-4377-977f-51c8f3a170c0</transaction-id><request-id>845ea3ed40b77f598a96441531395ba6</request-id><transaction-type>authorization</transaction-type><transaction-state>success</transaction-state><completion-time-stamp>2017-03-29T06:58:47.000Z</completion-time-stamp><statuses><status code="201.0000" description="The resource was successfully created." provider-transaction-id="87D646135U668492X" severity="information"/></statuses><requested-amount currency="EUR">12.590000</requested-amount><parent-transaction-id>a3bdd4e9-b0b2-4167-be62-7f09c1eb368f</parent-transaction-id><account-holder><first-name>Wirecardbuyer</first-name><last-name>Spintzyk</last-name><email>paypal.buyer2@wirecard.com</email></account-holder><shipping><first-name>Chlo</first-name><last-name>Li</last-name><address><street1>Milan</street1><city>MilAN</city><country>IT</country><postal-code>12234</postal-code></address></shipping><ip-address>127.0.0.1</ip-address><order-items><order-item><name>Item 1</name><description>My first item</description><article-number>A1</article-number><amount currency="EUR">2.590000</amount><quantity>1</quantity></order-item><order-item><name>Item 2</name><description>My second item</description><article-number>B2</article-number><amount currency="EUR">5.000000</amount><tax-amount currency="EUR">1.000000</tax-amount><quantity>2</quantity></order-item></order-items><notifications><notification url="http://localhost/PayPal/notify.php"/></notifications><payment-methods><payment-method name="paypal"/></payment-methods><api-id>---</api-id><cancel-redirect-url>http://localhost/PayPal/return.php?status=cancel</cancel-redirect-url><success-redirect-url>http://localhost/PayPal/return.php?status=success</success-redirect-url><wallet><account-id>ZNKTXUBNSQE2Y</account-id></wallet><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>J5r1OC85Cgy0zqTgW+G2IWJ6NEWPMh0uR123j+c7hEQ=</DigestValue></Reference></SignedInfo><SignatureValue>ddS79IgUfxrbI6Z/IbhltvNR60khscrVUKB1zcg3/CQv/Ow7SjrueGeM/EemoFW9pWoH5jh+q9FX5cpqeamButaxYNRjJTF/JAK5rz8zBNIrpyfHln3u3EmUMGAfHOFOlHjyg3azcXhwc8Kxg5CZsSOuho4At7h15UK34FhTClR2ae5DEmttZG6jLLbk0tlXnJTRq/OrrLETSoHB13Yyr34VNb/w7fyrKiycj6ls0RZK6OwukHFBjzqU15+CqYalvHLjeKmeEDy0z7kN5V7lQqK1iFqy0lKpdRNWME6bgkiHWz7AqBPElepaXfljH3uIC9ICSE3fZj0cFosVWqS5qujd6jviLgArPylbLzsPpmMr7uNEuGYn/JosbrGLmaEdOPx6n99QH9ZJEqhny+Hhb/yhCUOULzBTUThhryAp1K8OqVMHKr04Yr4WhO6qvWG0FRZscX9BDRlUVYv3af8rFB4vklrh40SzmX3agJ8dFwYwf1LXlc0224rzUL7IeogD1zlbtdUODvhyVIQ9eq/f0xyJ60MSo9Jo3iud+gTY7vX/Uv8OfOYycD12C5LlKAtLp1aoG9/8OO3SBwwb9V/3m9ALEajx1cgIZDe4E4AlMg65BWpiaQ4/QaRmNMH04K7xYXTp4AdCNXev0YIB2FdyID9MsKi28uJYnskxlSfwEXc=</SignatureValue><KeyInfo><X509Data><X509SubjectName>L=Ascheim,2.5.4.4=#130642617965726e,CN=api-test.wirecard.com,OU=Operations,O=Wirecard Technologies GmbH,C=DE</X509SubjectName><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></payment>'
            ]
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider signaturePublicKeyProvider
     * @param boolean $expected
     * @param string $publicKey
     * @param string $response
     */
    public function testValidateSignature($expected, $publicKey, $response)
    {
        /**
         * @var $config PHPUnit_Framework_MockObject_MockObject
         */
        $config = $this->config;
        $config->method('getPublicKey')->willReturn($publicKey);
        $this->config = $config;
        /**
         * @var SuccessResponse $mapped
         */
        $mapped = $this->mapper->mapInclSignature($response);
        $this->assertEquals($expected, $mapped->isValidSignature());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     * @param $jsonResponse
     */
    public function testMalformedResponseThrowsException($jsonResponse)
    {
        $this->mapper->map($jsonResponse, new PayPalTransaction());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testTransactionStateInProgressThrowsException()
    {
        $response = '<payment>
                        <transaction-state>in-progress</transaction-state>
                    </payment>';
        $this->mapper->map($response);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testMissingPaymentMethodsThrowsException()
    {
        $response = $this->getResponse($this->defaultResponseArray);
        $this->mapper->map($response, new PayPalTransaction());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testEmptyPaymentMethodsThrowsException()
    {
        $xmlResponse = $this->getResponse($this->defaultResponseArray, false);
        $xmlResponse->addChild('payment-methods');

        $this->mapper->map($xmlResponse->asXML(), new PayPalTransaction());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testMultiplePaymentMethodsThrowsException()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['payment-method'] = array('name' => 'a');
        $xmlResponse = $this->getResponse($responseArray, false);
        /**
         * @var $paymentMethods SimpleXMLElement
         */
        $paymentMethods = $xmlResponse->{'payment-methods'};
        $paymentMethods->addChild('payment-method');
        $this->mapper->map($xmlResponse->asXML(), new PayPalTransaction());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testMultipleDifferentProviderTransactionIDsThrowsException()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['statuses'] = array(
            ['code' => '305.0000',
                'description' => 'paypal:Status before.',
                'provider-transaction-id' => 'yyy',
                'severity' => 'information'],
            ['code' => '201.0000',
                'description' => 'paypal:The resource was successfully created.',
                'provider-transaction-id' => 'xxx',
                'severity' => 'information'
            ]
        );
        $response = $this->getResponse($this->defaultResponseArray);

        $this->mapper->map($response, new PayPalTransaction());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testMissingThreeDElementThrowsException()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['transaction-type'] = 'check-enrollment';
        $payload = $this->getResponse($responseArray);

        $transaction = new CreditCardTransaction();

        $this->mapper->map($payload, $transaction);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testMissingAcsElementThrowsException()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['transaction-type'] = 'check-enrollment';
        $responseArray['three-d'] = array('pareq' => 'request');
        $payload = $this->getResponse($responseArray);

        $this->mapper->map($payload, new CreditCardTransaction());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testMissingPareqElementThrowsException()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['transaction-type'] = 'check-enrollment';
        $responseArray['three-d'] = array('acs-url' => 'https://www.example.com/acs');
        $payload = $this->getResponse($responseArray);

        $this->mapper->map($payload, new CreditCardTransaction());
    }


    public function testGetSuccessRedirectUrlWithTransaction()
    {
        $responseArray = $this->defaultResponseArray;
        $responseArray['payment-method'] = array('name' => 'paypal');
        $response = $this->getResponse($responseArray);

        $redirect = new Redirect('http://success.ful', 'http://fail.ure');
        $transaction = new PayPalTransaction();
        $transaction->setRedirect($redirect);

        /**
         * @var $result FormInteractionResponse
         */
        $result = $this->mapper->map($response, $transaction);
        $this->assertEquals('http://success.ful', $result->getUrl());
    }


    public function malformedResponseProvider()
    {
        $fullData = [
            self::TRANSACTION_STATE => 'success',
            self::PAYMENT_METHODS => [
                self::PAYMENT_METHOD => [
                    self::ATTRIBUTES => [
                        self::PAYMENT_METHOD_URL => 'http://www.example.com/redirect'
                    ],
                ],
            ],
            self::STATUSES => [
                self::STATUS => [
                    self::ATTRIBUTES => [
                        self::STATUS_CODE => 200,
                        self::STATUS_DESCRIPTION => 'PHPUnit description',
                        self::STATUS_SEVERITY => 'information'
                    ]
                ]
            ]
        ];
        $cases = [
            [self::TRANSACTION_STATE],
            [self::TRANSACTION_ID],
            [self::STATUSES, self::STATUS, self::ATTRIBUTES, self::STATUS_CODE],
            [self::STATUSES, self::STATUS, self::ATTRIBUTES, self::STATUS_DESCRIPTION],
            [self::STATUSES, self::STATUS, self::ATTRIBUTES, self::STATUS_SEVERITY],
            [self::PAYMENT_METHODS, self::PAYMENT_METHOD, self::ATTRIBUTES]
        ];

        $providerData = [
            ['']
        ];

        foreach ($cases as $case) {
            $value = $this->arrayToXml($this->removeResponseKey($fullData, $case));
            $providerData[] = [$value];
        }
        return $providerData;
    }

    private function arrayToXml($data, &$xmlData = null)
    {
        if ($xmlData === null) {
            $xmlData = new \SimpleXMLElement('<payment></payment>');
        }
        foreach ($data as $key => $value) {
            if ($key === self::ATTRIBUTES) {
                foreach ($value as $attribute => $content) {
                    $xmlData->addAttribute($attribute, $content);
                }
            } elseif (is_array($value)) {
                $subnode = $xmlData->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }
        return $xmlData->asXML();
    }

    private function removeResponseKey($response, array $key)
    {
        if (count($key) > 1) {
            $mainKey = array_shift($key);
            $subResponse = $response[$mainKey];
            $response[$mainKey] = $this->removeResponseKey($subResponse, $key);
        } else {
            unset($response[$key[0]]);
        }
        return $response;
    }
}
