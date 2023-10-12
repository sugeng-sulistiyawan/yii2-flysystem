<?php

namespace diecoding\flysystem;

use diecoding\flysystem\traits\UrlGeneratorTrait;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

/**
 * Interacting with an sftp filesystem
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
 *         'privateKey' => '/path/to/my/private_key', // private key (optional, default: null) can be used instead of password, set to null if password is set
 *         'passphrase' => 'super-secret-password', // passphrase (optional, default: null), set to null if privateKey is not used or has no passphrase
 *         'port' => 22,
 *         'useAgent' => true,
 *         'timeout' => 10,
 *         'maxTries' => 4,
 *         'hostFingerprint' => null,
 *         'connectivityChecker' => null, // connectivity checker (must be an implementation of `League\Flysystem\PhpseclibV2\ConnectivityChecker` to check if a connection can be established (optional, omit if you don't need some special handling for setting reliable connections)
 *         'preferredAlgorithms' => [],
 *         'root' => '/root/path/', // or you can use @alias
 *         'action' => '/site/file',
 *         'prefix' => '', 
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
    use UrlGeneratorTrait;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string|null
     */
    public $password = null;

    /**
     * @var string|null
     */
    public $privateKey = null;

    /**
     * @var string|null
     */
    public $passphrase = null;

    /**
     * @var int
     */
    public $port = 22;

    /**
     * @var bool
     */
    public $useAgent = false;

    /**
     * @var int
     */
    public $timeout = 10;
    /**
     * @var int
     */
    public $maxTries = 4;

    /**
     * @var string|null
     */
    public $hostFingerprint = null;

    /**
     * @var \League\Flysystem\PhpseclibV3\ConnectivityChecker|null
     */
    public $connectivityChecker = null;

    /**
     * @var array
     */
    public $preferredAlgorithms = [];

    /**
     * @var string
     */
    public $root;

    /**
     * @var array
     */
    protected $_properties = [
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
        $this->passphrase = $this->passphrase ?: ($this->password ?: ($this->username ?: Yii::$app->id));
        $this->initEncrypter($this->passphrase);

        parent::init();
    }

    /**
     * @return SftpAdapter
     */
    protected function initAdapter()
    {
        $this->root = (string) Yii::getAlias($this->root);

        $options = [];
        foreach ($this->_properties as $property) {
            $options[$property] = $this->$property;
        }

        $this->_connectionProvider = SftpConnectionProvider::fromArray($options);

        $adapter = new SftpAdapter($this->_connectionProvider, $this->root);
        if ($this->prefix) {
            $adapter = new PathPrefixedAdapter($adapter, $this->prefix);
        }

        return $adapter;
    }
}