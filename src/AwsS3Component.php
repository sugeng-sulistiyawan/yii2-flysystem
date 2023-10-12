<?php

namespace diecoding\flysystem;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use yii\base\InvalidConfigException;

/**
 * Interacting with Aws S3
 * @see https://flysystem.thephpleague.com/docs/adapter/aws-s3-v3/
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class'    => \diecoding\flysystem\AwsS3Component::class,
 *         'endpoint' => 'http://your-endpoint',
 *         'key'      => 'your-key',
 *         'secret'   => 'your-secret',
 *         'bucket'   => 'your-bucket',
 *         'prefix'   => '',
 *         // 'region'               => 'us-east-1'
 *         // 'version'              => 'latest',
 *         // 'usePathStyleEndpoint' => false,
 *         // 'streamReads'          => false,
 *         // 'options'              => [],
 *         // 'credentials'          => [],
 *         // 'debug'                => false,
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
    public $endpoint;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $secret;

    /**
     * @var string
     */
    public $bucket;

    /**
     * @var string
     */
    public $region = 'us-east-1';

    /**
     * @var string
     */
    public $version = 'latest';

    /**
     * @var bool
     */
    public $usePathStyleEndpoint = false;

    /**
     * @var bool
     */
    public $streamReads = false;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var array
     */
    public $credentials = [];

    /**
     * @var bool
     */
    public $debug = false;

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
     * @return AwsS3V3Adapter
     */
    protected function initAdapter()
    {
        if (empty($this->credentials)) {
            $config['credentials'] = [
                'key' => $this->key,
                'secret' => $this->secret,
            ];
        } else {
            $config['credentials'] = $this->credentials;
        }

        $config['endpoint'] = $this->endpoint;
        $config['use_path_style_endpoint'] = $this->usePathStyleEndpoint;
        $config['region'] = $this->region;
        $config['version'] = $this->version;
        $config['debug'] = $this->debug;

        /**
         * {@see S3Client::__construct}, S3Client accepts the following
         * {@see Aws\AwsClient::__construct}, S3Client accepts the following
         */
        $this->client = new S3Client($config);

        return new AwsS3V3Adapter($this->client, $this->bucket, $this->prefix, null, null, $this->options, $this->streamReads);
    }
}