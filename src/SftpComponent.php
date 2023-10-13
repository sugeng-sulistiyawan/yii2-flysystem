<?php

namespace diecoding\flysystem;

use diecoding\flysystem\adapter\SftpAdapter;
use diecoding\flysystem\traits\UrlGeneratorComponentTrait;
use League\Flysystem\Config;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Interacting with an SFTP filesystem
 * This implementation uses version 3 of phpseclib
 * @see https://flysystem.thephpleague.com/docs/adapter/sftp-v3/
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\SftpComponent::class,
 *         'host' => 'hostname',
 *         'username' => 'username',
 *         'password' => null, // password (optional, default: null) set to null if privateKey is used
 *         // 'privateKey' => '/path/to/my/private_key', // private key (optional, default: null) can be used instead of password, set to null if password is set
 *         // 'passphrase' => 'super-secret-password', // passphrase (optional, default: null), set to null if privateKey is not used or has no passphrase
 *         // 'port' => 22,
 *         // 'useAgent' => true,
 *         // 'timeout' => 10,
 *         // 'maxTries' => 4,
 *         // 'hostFingerprint' => null,
 *         // 'connectivityChecker' => null, // connectivity checker (must be an implementation of `League\Flysystem\PhpseclibV2\ConnectivityChecker` to check if a connection can be established (optional, omit if you don't need some special handling for setting reliable connections)
 *         // 'preferredAlgorithms' => [],
 *         // 'root' => '/root/path/', // or you can use @alias
 *         // 'action' => '/site/file', // action route
 *         // 'prefix' => '',
 *     ],
 * ],
 * ```
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
class SftpComponent extends AbstractComponent
{
    use UrlGeneratorComponentTrait;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $privateKey;

    /**
     * @var string
     */
    public $passphrase;

    /**
     * @var int
     */
    public $port;

    /**
     * @var bool
     */
    public $useAgent;

    /**
     * @var int
     */
    public $timeout;
    /**
     * @var int
     */
    public $maxTries;

    /**
     * @var string
     */
    public $hostFingerprint;

    /**
     * @var \League\Flysystem\PhpseclibV3\ConnectivityChecker
     */
    public $connectivityChecker;

    /**
     * @var array
     */
    public $preferredAlgorithms;

    /**
     * @var string
     */
    public $root;

    /**
     * @var string[]
     */
    protected $_availableOptions = [
        'host',
        'username',
        'password',
        'privateKey',
        'passphrase',
        'port',
        'useAgent',
        'timeout',
        'maxTries',
        'hostFingerprint',
        'connectivityChecker',
        'preferredAlgorithms',
    ];

    /**
     * @var SftpConnectionProvider
     */
    protected $_connectionProvider;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->host)) {
            throw new InvalidConfigException('The "host" property must be set.');
        }
        if (empty($this->username)) {
            throw new InvalidConfigException('The "username" property must be set.');
        }

        $this->passphrase = $this->passphrase ?: ($this->password ?: ($this->username ?: Yii::$app->id));
        $this->initEncrypter($this->passphrase);

        parent::init();
    }

    /**
     * @return SftpAdapter|PathPrefixedAdapter
     */
    protected function initAdapter()
    {
        $this->root = (string) Yii::getAlias($this->root);

        $options = [];
        foreach ($this->_availableOptions as $property) {
            if ($this->$property !== null) {
                $options[$property] = $this->$property;
            }
        }

        $this->_connectionProvider = SftpConnectionProvider::fromArray($options);

        $adapter = new SftpAdapter($this->_connectionProvider, $this->root);
        // for UrlGeneratorAdapterTrait
        $adapter->component = $this;

        if ($this->prefix) {
            $adapter = new PathPrefixedAdapter($adapter, $this->prefix);
        }

        return $adapter;
    }
}