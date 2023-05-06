<?php

namespace diecoding\flysystem;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use yii\base\InvalidConfigException;

/**
 * Class AwsS3Component
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\AwsS3Component::class,
 *         'endpoint' => 'my-endpoint',
 *         'key' => 'my-key',
 *         'secret' => 'my-secret',
 *         'bucket' => 'my-bucket',
 *         'prefix' => '',
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
        $config = [];

        if (empty($this->credentials)) {
            $config['credentials'] = [
                'key'    => $this->key,
                'secret' => $this->secret,
            ];
        } else {
            $config['credentials'] = $this->credentials;
        }

        $config['endpoint']                = $this->endpoint;
        $config['use_path_style_endpoint'] = $this->usePathStyleEndpoint;
        $config['region']                  = $this->region;
        $config['version']                 = $this->version;

        $this->client = new S3Client($config);

        return new AwsS3V3Adapter($this->client, $this->bucket, $this->prefix, null, null, $this->options, $this->streamReads);
    }
}
