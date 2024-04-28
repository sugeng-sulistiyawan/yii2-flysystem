<?php

namespace diecoding\flysystem;

use AsyncAws\S3\S3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use yii\base\InvalidConfigException;

/**
 * Interacting with Aws S3 (Async) filesystem
 * Read more about AsyncAws's S3Client in [their documentation](https://async-aws.com/clients/s3.html).
 * @see https://flysystem.thephpleague.com/docs/adapter/aws-s3-v3/
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\AsyncAwsS3Component::class,
 *         'endpoint' => 'http://your-endpoint',
 *         'bucket' => 'my-bucket',
 *         'accessKeyId' => 'my-key',
 *         'accessKeySecret' => 'my-secret',
 *         // 'sharedCredentialsFile' => '~/.aws/credentials',
 *         // 'sharedConfigFile' => '~/.aws/config',
 *         // 'region' => 'us-east-1',
 *         // 'endpointDiscoveryEnabled' => false,
 *         // 'pathStyleEndpoint' => false,
 *         // 'sendChunkedBody' => false,
 *         // 'debug' => false,
 *         // 'prefix' => '',
 *     ],
 * ],
 * ```
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
class AsyncAwsS3Component extends AbstractComponent
{
    /**
     * @var string
     */
    public $bucket;

    /**
     * @var string
     */
    public $region;

    /**
     * @var string
     */
    public $accessKeyId;

    /**
     * @var string
     */
    public $accessKeySecret;

    /**
     * @var string
     */
    public $sharedCredentialsFile;

    /**
     * @var string
     */
    public $sharedConfigFile;

    /**
     * @var string
     */
    public $endpoint;

    /**
     * @var bool
     */
    public $endpointDiscoveryEnabled;

    /**
     * @var bool
     */
    public $pathStyleEndpoint;

    /**
     * @var bool
     */
    public $sendChunkedBody;

    /**
     * @var S3Client
     */
    protected $client;

    /**
     * @var bool[]
     */
    protected $_availableOptions = [
        'region' => false,
        'debug' => true,
        'accessKeyId' => false,
        'accessKeySecret' => false,
        'sharedCredentialsFile' => false,
        'sharedConfigFile' => false,
        'endpoint' => false,
        'endpointDiscoveryEnabled' => true,
        'pathStyleEndpoint' => true,
        'sendChunkedBody' => true,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->accessKeyId)) {
            throw new InvalidConfigException('The "accessKeyId" property must be set.');
        }
        if (empty($this->accessKeySecret)) {
            throw new InvalidConfigException('The "accessKeySecret" property must be set.');
        }
        if (empty($this->bucket)) {
            throw new InvalidConfigException('The "bucket" property must be set.');
        }

        parent::init();
    }

    /**
     * @return AsyncAwsS3Adapter
     */
    protected function initAdapter()
    {
        $config = [];
        foreach ($this->_availableOptions as $property => $export) {
            if ($this->$property !== null) {
                $config[$property] = $export ? var_export($this->$property, true) : $this->$property;
            }
        }

        $this->client = new S3Client($config);

        return new AsyncAwsS3Adapter($this->client, $this->bucket, (string) $this->prefix);
    }
}
