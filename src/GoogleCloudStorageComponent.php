<?php

namespace diecoding\flysystem;

use Google\Auth\FetchAuthTokenInterface;
use Google\Cloud\Storage\StorageClient;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use Psr\Cache\CacheItemPoolInterface;
use yii\base\InvalidConfigException;

/**
 * Interacting with Google Cloud Storage filesystem
 * @see https://flysystem.thephpleague.com/docs/adapter/google-cloud-storage/
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\GoogleCloudStorageComponent::class,
 *         'bucket' => 'your-bucket',
 *         // 'apiEndpoint' => '',
 *         // 'projectId' => '',
 *         // 'authCache' => null,
 *         // 'authCacheOptions' => [],
 *         // 'authHttpHandler' => function () {},
 *         // 'credentialsFetcher' => null,
 *         // 'httpHandler' => function () {},
 *         // 'keyFile' => '',
 *         'keyFilePath' => __DIR__ . '/gcs_credentials.json',
 *         // 'requestTimeout' => 0,
 *         // 'retries' => 0,
 *         // 'scopes' => [],
 *         // 'quotaProject' => '',
 *         // 'userProject' => false,
 *         // 'debug' => false,
 *         // 'prefix' => '',
 *     ],
 * ],
 * ```
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2024
 */
class GoogleCloudStorageComponent extends AbstractComponent
{
    /**
     * @var string The name of the bucket to request.
     */
    public $bucket;

    /**
     * @var string The hostname with optional port to use in
     *             place of the default service endpoint. Example:
     *             `foobar.com` or `foobar.com:1234`.
     */
    public $apiEndpoint;

    /**
     * @var string The project ID from the Google Developer's
     *             Console.
     */
    public $projectId;

    /**
     * @var CacheItemPoolInterface A cache used storing access
     *                             tokens. **Defaults to** a simple in memory implementation.
     */
    public $authCache;

    /**
     * @var array Cache configuration options.
     */
    public $authCacheOptions;

    /**
     * @var callable A handler used to deliver Psr7
     *               requests specifically for authentication.
     */
    public $authHttpHandler;

    /**
     * @var FetchAuthTokenInterface A credentials fetcher instance.
     */
    public $credentialsFetcher;

    /**
     * @var callable A handler used to deliver Psr7 requests.
     *               Only valid for requests sent over REST.
     */
    public $httpHandler;

    /**
     * @var array The contents of the service account credentials
     *            .json file retrieved from the Google Developer's Console.
     *            Ex: `json_decode(file_get_contents($path), true)`.
     */
    public $keyFile;

    /**
     * @var string The full path to your service account
     *             credentials .json file retrieved from the Google Developers
     *             Console.
     */
    public $keyFilePath;

    /**
     * @var float Seconds to wait before timing out the
     *            request. **Defaults to** `0` with REST and `60` with gRPC.
     */
    public $requestTimeout;

    /**
     * @var int Number of retries for a failed request.
     *          **Defaults to** `3`.
     */
    public $retries;

    /**
     * @var array Scopes to be used for the request.
     */
    public $scopes;

    /**
     * @var string Specifies a user project to bill for
     *             access charges associated with the request.
     */
    public $quotaProject;

    /**
     * @var string|bool $userProject If true, the current Project ID
     *                  will be used. If a string, that string will be used as the
     *                  userProject argument, and that project will be billed for the
     *                  request. **Defaults to** `false`.
     */
    public $userProject = false;

    /**
     * @var StorageClient
     */
    protected $client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->bucket)) {
            throw new InvalidConfigException('The "bucket" property must be set.');
        }

        parent::init();
    }

    /**
     * @return GoogleCloudStorageAdapter
     */
    protected function initAdapter()
    {
        $config = [
            'apiEndpoint' => $this->apiEndpoint,
            'projectId' => $this->projectId,
            'authCache' => $this->authCache,
            'authCacheOptions' => $this->authCacheOptions,
            'authHttpHandler' => $this->authHttpHandler,
            'credentialsFetcher' => $this->credentialsFetcher,
            'httpHandler' => $this->httpHandler,
            'keyFile' => $this->keyFile,
            'keyFilePath' => $this->keyFilePath,
            'requestTimeout' => $this->requestTimeout,
            'retries' => $this->retries,
            'scopes' => $this->scopes,
            'quotaProject' => $this->quotaProject,
        ];

        foreach ($config as $key => $value) {
            if (empty($value)) {
                unset($config[$key]);
            }
        }

        $this->client = new StorageClient($config);
        $bucket = $this->client->bucket($this->bucket, $this->userProject);

        return new GoogleCloudStorageAdapter($bucket, (string) $this->prefix);
    }
}
