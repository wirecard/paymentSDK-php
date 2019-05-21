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

namespace Wirecard\PaymentSdk\Services;

class FileSystemCache implements Cache
{

    /** @var string folder name to store cache file into */
    private $cacheFolder;
    /** @var string filename of cachefile inside $this->cacheFolder */
    private $cacheFile;

    /**
     * FileSystemCache constructor.
     *
     * Filename is mandatory, folder name optionally.
     *
     * @since 3.7.0
     * @param string $cacheFile Name of the file inside $cacheFolder
     * @param string|null $cacheFolder Cachefolder, null to use system temp dir as default
     */
    public function __construct($cacheFile, $cacheFolder = null)
    {
        if (empty($cacheFolder)) {
            $cacheFolder = sys_get_temp_dir();
        }
        $this->cacheFolder = $cacheFolder;
        $this->cacheFile   = $cacheFile;
    }

    /**
     * Read a specific value from cache
     *
     * The type of the returned value depends from the value
     * last stored into cache by this key.
     *
     * Supported payload types: string|array|integer|double
     *
     * If entry not found, null is returned
     *
     * @since 3.7.0
     * @param string $key Key to access the specific value
     * @return mixed|null Stored payload, or null if no value found
     */
    public function read($key)
    {
        $cacheData = $this->readData();
        if (array_key_exists($key, $cacheData)) {
            return $cacheData[$key];
        }
        return null;
    }

    /**
     * Store a specific value in cache
     *
     * Supported payload types: string|array|integer|double
     *
     * @since 3.7.0
     * @param string $key Key to access the specific value
     * @param mixed $payload Payload to store
     */
    public function store($key, $payload)
    {
        $cacheData       = $this->readData();
        $cacheData[$key] = $payload;
        $this->writeData($cacheData);
    }

    /**
     * Remove a specific value from cache.
     *
     * @since 3.7.0
     * @param string $key Key to access the specific value
     */
    public function remove($key)
    {
        $cacheData = $this->readData();
        if (array_key_exists($key, $cacheData)) {
            unset($cacheData[$key]);
            $this->writeData($cacheData);
        }
    }

    /**
     * Clear the complete cache
     *
     * @since 3.7.0
     */
    public function clear()
    {
        $cacheData = [];
        $this->writeData($cacheData);
    }

    /**
     * Build the full path name to cachefile (folder + filename)
     *
     * @since 3.7.0
     * @return string
     */
    private function buildCacheFileName()
    {
        return $this->cacheFolder . DIRECTORY_SEPARATOR . $this->cacheFile;
    }

    /**
     * Rewrite complete cache at once
     *
     * The cache itself is a map $key => $payload with one entry per
     * cache line.
     *
     * An empty array also is allowed
     *
     * NOTE: There is no notification if cache is misconfigured and
     * not available!
     *
     * @since 3.7.0
     * @param array $newCacheData complete cache content
     */
    private function writeData(array $newCacheData)
    {
        $filename    = $this->buildCacheFileName();
        $isWriteable = (
            (file_exists($filename) && is_writable($filename))
            || (!file_exists($filename) && is_writable($this->cacheFolder))
        );

        if ($isWriteable) {
            file_put_contents($filename, json_encode($newCacheData), LOCK_EX);
        }
    }

    /**
     * Read the complete cache at once
     *
     * The result is a map $key => $payload with one entry per
     * cache line.
     *
     * @since 3.7.0
     * @return array always an array, maybe an empty one
     */
    private function readData()
    {
        $filename = $this->buildCacheFileName();
        if (file_exists($filename)) {
            $rawContent  = file_get_contents($filename);
            $decodedData = json_decode($rawContent, JSON_OBJECT_AS_ARRAY);
            if (!empty($decodedData)) {
                return $decodedData;
            }
        }
        return [];
    }
}
