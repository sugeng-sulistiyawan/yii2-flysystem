<?php

namespace diecoding\flysystem;

use Google\Client;
use Google\Service\Drive;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use Masbug\Flysystem\GoogleDriveAdapter;
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
class GoogleDriveComponent extends AbstractComponent
{
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
