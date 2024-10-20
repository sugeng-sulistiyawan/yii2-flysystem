<?php

namespace diecoding\flysystem;

use diecoding\flysystem\adapter\GoogleDriveAdapter;
use diecoding\flysystem\traits\UrlGeneratorComponentTrait;
use Google\Client;
use Google\Service\Drive;
use yii\base\InvalidConfigException;

/**
 * Interacting with Google Drive filesystem
 * @see https://github.com/masbug/flysystem-google-drive-ext
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\GoogleDriveComponent::class,
 *         'applicationName' => 'My Google Drive App',
 *         'clientId' => '',
 *         'clientSecret' => '',
 *         'refreshToken' => '',
 *         // 'teamDriveId' => '',
 *         // 'sharedFolderId' => '',
 *         // 'options' => [],
 *         'secret' => 'my-secret', // for secure route url
 *         // 'action' => '/site/file', // action route
 *         // 'prefix' => '',
 *     ],
 * ],
 * ```
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2024
 */
class GoogleDriveComponent extends AbstractComponent
{
    use UrlGeneratorComponentTrait;

    /**
     * @var string
     */
    public $applicationName;

    /**
     * @var string
     */
    public $clientId;

    /**
     * @var string
     */
    public $clientSecret;

    /**
     * @var string
     */
    public $refreshToken;

    /**
     * @var string
     */
    public $teamDriveId;

    /**
     * @var string
     */
    public $sharedFolderId;

    /**
     * @var string
     */
    public $secret;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string[]
     */
    protected $_availableOptions = [
        'teamDriveId',
        'sharedFolderId',
    ];

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->validateProperties([
            'clientId',
            'clientSecret',
            'refreshToken',
            'secret',
        ]);

        $this->initEncrypter($this->secret);

        parent::init();
    }

    /**
     * @return GoogleDriveAdapter
     */
    protected function initAdapter()
    {
        $client = new Client();
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->refreshToken(refreshToken: $this->refreshToken);

        if (!empty($this->applicationName)) {
            $client->setApplicationName($this->applicationName);
        }

        foreach ($this->_availableOptions as $property) {
            if (!empty($this->$property)) {
                $this->options[$property] = $this->$property;
            }
        }

        $this->client = $client;
        $service = new Drive($this->client);

        $adapter = new GoogleDriveAdapter($service, $this->prefix, $this->options);
        // for UrlGeneratorAdapterTrait
        $adapter->component = $this;

        return $adapter;
    }
}
