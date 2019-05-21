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

use Http\Client\Common\HttpMethodsClient;
use Http\Message\MessageFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

class CachedBackendLanguageService implements BackendLanguagesService
{

    /** @var string internal key to access the cachetime of a cache hit */
    const KEY_CACHETIME = 'cachetime';
    /** @var string internal key to access the payload of a cache hit */
    const KEY_PAYLOAD   = 'payload';

    /** @var Cache cache implementation */
    private $cache;
    /** @var HttpMethodsClient HTTP client instance */
    private $httpClient;
    /** @var LoggerInterface logger instance */
    private $logger;
    /** @var MessageFactory message factory instance */
    private $messageFactory;

    /**
     * CachedBackendLanguageService constructor.
     *
     * The default service implementation expects some other objects to re-use
     * it instead instanciate an own copy.
     *
     * @since 3.7.0
     * @param HttpMethodsClient $httpClient   PSR-18 Http Client
     * @param MessageFactory $messageFactory  PSR-7 Message Factory
     * @param Cache $cache                    Cache implementation
     * @param LoggerInterface|null $logger    PSR-3 Logger Interface
     */
    public function __construct(
        HttpMethodsClient $httpClient,
        MessageFactory $messageFactory,
        Cache $cache,
        LoggerInterface $logger = null
    ) {

        $this->cache          = $cache;
        $this->httpClient     = $httpClient;
        $this->logger         = $logger;
        $this->messageFactory = $messageFactory;
    }

    /**
     * Implementation of interface method:
     *
     * Return a JSON map of all supported backend languages.
     *
     * The result is a map $languageCode => $languageName encapsulated
     * in JSON.
     *
     * With $cacheTtlInMinutes it is configurable, which old data are
     * accepted directly from cache.
     *
     * Use $cacheTtlInMinutes = 0 to disable the cache
     *
     * If the service not available/not reachable, null is returned.
     *
     * @since 3.7.0
     * @param string $baseUrl baseUrl of gateway to check languages
     * @param int|double $cacheTtlInMinutes time to live for a valid result, 0 will disable caching
     * @return string|null JSON string contains backend languages, or null
     */
    public function getBackendLanguages($baseUrl, $cacheTtlInMinutes)
    {
        if ($cacheTtlInMinutes > 0) {
            $cachedResult = $this->fetchResultFromCache($baseUrl, $cacheTtlInMinutes);
            if (!empty($cachedResult)) {
                return $cachedResult;
            }
        }

        $webResult = $this->fetchResultFromWeb($baseUrl);
        if (!empty($webResult) && $cacheTtlInMinutes > 0) {
            $this->storeResultInCache($baseUrl, $webResult);
        }
        return $webResult;
    }

    /**
     * Helper method to fetch a result from cache
     *
     * With $cacheTtlInMinutes a found cache hit is checked if the payload
     * outdated or not.
     *
     * @since 3.7.0
     * @param string $baseUrl
     * @param int|double $cacheTtlInMinutes time to live for a valid result
     * @return string|null JSON string contains backend languages, or null
     */
    private function fetchResultFromCache($baseUrl, $cacheTtlInMinutes)
    {
        if (empty($this->cache)) {
            return null;
        }

        $cacheData = $this->cache->read($baseUrl);
        if (empty($cacheData)) {
            return null;
        }

        $allowedCacheTimestamp = time() - ($cacheTtlInMinutes * 60);
        if ($cacheData[self::KEY_CACHETIME] < $allowedCacheTimestamp) {
            return null;
        }

        return $cacheData[self::KEY_PAYLOAD];
    }

    /**
     * Helper method to store a specific entry in cache
     *
     * $baseUrl is used as key, the cache time is automatically added so
     * later access to cache is able to check if result outdated or not
     *
     * @since 3.7.0
     * @param string $baseUrl here used as cache key
     * @param string $backendLanguages JSON encoded map of backend languages
     */
    private function storeResultInCache($baseUrl, $backendLanguages)
    {
        $cachedData = [
            self::KEY_CACHETIME => time(),
            self::KEY_PAYLOAD   => $backendLanguages,
        ];
        $this->cache->store($baseUrl, $cachedData);
    }

    /**
     * Helper method to fetch a result from web
     *
     * If web service not available null is returned.
     *
     * @since 3.7.0
     * @param string $baseUrl
     * @return string|null JSON string contains backend languages, or null
     */
    private function fetchResultFromWeb($baseUrl)
    {
        $url = $baseUrl . '/engine/includes/i18n/languages/hpplanguages.json';
        try {
            $request = $this->messageFactory->createRequest('GET', $url);
            return $this->httpClient->sendRequest($request)->getBody()->getContents();
        } catch (ClientExceptionInterface $hce) {
            if (!empty($this->logger)) {
                $errMsg = 'fetchResultFromWeb: Cannot reach %s: %s';
                $this->logger->warn(sprintf($errMsg, $url, $hce->getMessage()));
            }
            return null;
        } catch (Exception $ex) {
            if (!empty($this->logger)) {
                $errMsg = 'fetchResultFromWeb: problem getting languages: %s (%s)';
                $this->logger->warn(sprintf($errMsg, get_class($ex), $ex->getMessage()));
            }
            return null;
        }
    }
}
