<?php

namespace diecoding\flysystem;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use yii\base\InvalidConfigException;

/**
 * Class AwsS3Component
 *
 * @package diecoding\flysystem
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\AwsS3Component::class,
 *         'endpoint' => 'my-endpoint',
 *         'credentials' => [ // array|\Aws\CacheInterface|\Aws\Credentials\CredentialsInterface|bool|callable
 *             'key' => 'my-key',
 *             'secret' => 'my-secret',
 *         ],
 *         'bucket' => 'my-bucket',
 *         'region' => 'us-east-1',
 *         'version' => 'latest',
 *         'usePathStyleEndpoint' => true,
 *         'basePath' => '',
 *     ],
 * ],
 * ```
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
class AwsS3Component extends AbstractComponent
{
    /**
     * @var string
     */
    public $endpoint = '';

    /**
     * @var string
     */
    public $key = '';

    /**
     * @var string
     */
    public $secret = '';

    /**
     * @var string
     */
    public $region = '';

    /**
     * @var string
     */
    public $version = 'latest';

    /**
     * @var string
     */
    public $bucket = '';

    /**
     * @var bool
     */
    public $usePathStyleEndpoint = false;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var bool
     */
    public $streamReads = false;

    /**
     * @var array|\Aws\CacheInterface|\Aws\Credentials\CredentialsInterface|bool|callable
     */
    public $credentials;

    /**
     * @var S3Client
     */
    protected $client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->credentials)) {
            if (empty($this->key)) {
                throw new InvalidConfigException('The "key" property must be set.');
            }

            if (empty($this->secret)) {
                throw new InvalidConfigException('The "secret" property must be set.');
            }
        }

        if (empty($this->bucket)) {
            throw new InvalidConfigException('The "bucket" property must be set.');
        }

        parent::init();
    }

    /**
     * Get a URL
     * 
     * @param string $filePath
     * @return string
     */
    public function getUrl(string $filePath)
    {
        return $this->client->getObjectUrl($this->bucket, $this->normalizePath($filePath));
    }

    /**
     * Get a pre-signed URL
     * 
     * @param string $filePath
     * @param int|string|\DateTimeInterface $expires
     * @return string
     */
    public function getPresignedUrl(string $filePath, $expires = '+5 Minutes')
    {
        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $this->normalizePath($filePath),
        ]);
        $presignedRequest = $this->client->createPresignedRequest($command, $expires);

        return (string) $presignedRequest->getUri();
    }

    /**
     * @return AwsS3V3Adapter
     */
    protected function initAdapter()
    {
        $config = [];

        if (empty($this->credentials)) {
            $config['credentials'] = ['key' => $this->key, 'secret' => $this->secret];
        } else {
            $config['credentials'] = $this->credentials;
        }

        $this->key    = $config['credentials']['key'];
        $this->secret = $config['credentials']['secret'];

        if ($this->usePathStyleEndpoint === true) {
            $config['use_path_style_endpoint'] = true;
        }

        if (!empty($this->region)) {
            $config['region'] = $this->region;
        }

        if (!empty($this->endpoint)) {
            $config['endpoint'] = $this->endpoint;
        }

        $config['version'] = $this->version;

        $this->client = new S3Client($config);

        return new AwsS3V3Adapter($this->client, $this->bucket, '', null, null, $this->options, $this->streamReads);
    }
}
