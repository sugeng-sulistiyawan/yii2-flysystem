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
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->clientId)) {
            throw new InvalidConfigException('The "clientId" property must be set.');
        }

        if (empty($this->clientSecret)) {
            throw new InvalidConfigException('The "clientSecret" property must be set.');
        }

        if (empty($this->refreshToken)) {
            throw new InvalidConfigException('The "refreshToken" property must be set.');
        }

        if (!empty($this->teamDriveId)) {
            $this->options['teamDriveId'] = $this->teamDriveId;
        }

        if (!empty($this->sharedFolderId)) {
            $this->options['sharedFolderId'] = $this->sharedFolderId;
        }

        if (empty($this->secret)) {
            throw new InvalidConfigException('The "secret" property must be set.');
        }

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

        $this->client = $client;
        $service = new Drive($this->client);

        return new GoogleDriveAdapter($service, $this->prefix, $this->options);
    }
}
