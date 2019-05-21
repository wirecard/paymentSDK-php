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

namespace WirecardTest\PaymentSdk\Services;

use PHP_CodeSniffer\Util\Cache;
use Wirecard\PaymentSdk\Services\FileSystemCache;

class TransactionServiceUTest extends \PHPUnit_Framework_TestCase
{

    const UNIT_CACHE_FILE = 'utest.json';
    const TEST_KEY1       = 'test_key';
    const TEST_PAYLOAD1   = 'test_payload';
    const TEST_KEY2       = 'another_test_key';
    const TEST_PAYLOAD2   = 'second time is best time';

    private $classUnderTest;

    public function setUp()
    {
        $this->classUnderTest = new FileSystemCache(self::UNIT_CACHE_FILE);
        $this->classUnderTest->clear();
        $this->classUnderTest->store(self::TEST_KEY1, self::TEST_PAYLOAD1);
        $this->classUnderTest->store(self::TEST_KEY2, self::TEST_PAYLOAD2);
    }

    public function testReadExistingValue()
    {
        $this->assertEquals(self::TEST_PAYLOAD1, $this->classUnderTest->read(self::TEST_KEY1));
    }

    public function testReadNonExistingKey()
    {
        $this->assertNull($this->classUnderTest->read('nonExistingKey'));
    }

    public function testStoreKey()
    {
        $key   = 'storeTest';
        $value = 'always look on the bright side of life';
        $this->classUnderTest->store($key, $value);
        $this->assertEquals($value, $this->classUnderTest->read($key));
    }

    public function testStoreNonStrings()
    {
        $testData = [
            'int'   => 0xCAFE,
            'float' => -1.2,
            'list'  => ['Harry', 'Ron', 'Hermione'],
            'map'   => ['green' => '#0F0', 'blue' => '#00F'],
        ];
        foreach ($testData as $key => $value) {
            $this->classUnderTest->store($key, $value);
        }

        $this->assertEquals($testData['int'], $this->classUnderTest->read('int'));
        $this->assertTrue(is_integer($this->classUnderTest->read('int')));

        $this->assertEquals($testData['float'], $this->classUnderTest->read('float'));
        $this->assertTrue(is_numeric($this->classUnderTest->read('float')));

        $this->assertEquals($testData['list'], $this->classUnderTest->read('list'));
        $this->assertTrue(is_array($this->classUnderTest->read('list')));

        $this->assertEquals($testData['map'], $this->classUnderTest->read('map'));
        $this->assertTrue(is_array($this->classUnderTest->read('map')));
    }

    public function testRemoveKey()
    {
        $this->classUnderTest->remove(self::TEST_KEY1);
        $this->assertNull($this->classUnderTest->read(self::TEST_KEY1));
        $this->assertEquals(self::TEST_PAYLOAD2, $this->classUnderTest->read(self::TEST_KEY2));
    }

    public function testClearCache()
    {
        $this->classUnderTest->clear();
        $this->assertNull($this->classUnderTest->read(self::TEST_KEY1));
        $this->assertNull($this->classUnderTest->read(self::TEST_KEY2));
    }
}
