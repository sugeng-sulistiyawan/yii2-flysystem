<?php

namespace diecoding\flysystem;

use diecoding\flysystem\adapter\FtpAdapter;
use diecoding\flysystem\traits\UrlGeneratorComponentTrait;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use Yii;

/**
 * Interacting with an FTP filesystem
 * @see https://flysystem.thephpleague.com/docs/adapter/ftp/
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\FtpComponent::class,
 *         'host' => 'hostname',
 *         'root' => '/root/path/', // or you can use @alias
 *         'username' => 'username',
 *         'password' => 'password',
 *         // 'port' => 21,
 *         // 'ssl' => false,
 *         // 'timeout' => 90,
 *         // 'utf8' => false,
 *         // 'passive' => true,
 *         // 'transferMode' => FTP_BINARY,
 *         // 'systemType' => null, // 'windows' or 'unix'
 *         // 'ignorePassiveAddress' => null, // true or false
 *         // 'timestampsOnUnixListingsEnabled' => false,
 *         // 'recurseManually' => true,
 *         // 'useRawListOptions' => null, // true or false
 *         // 'passphrase' => 'secret', // for secure route url
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
class FtpComponent extends AbstractComponent
{
    use UrlGeneratorComponentTrait;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $root;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var int
     */
    public $port;

    /**
     * @var bool
     */
    public $ssl;

    /**
     * @var int
     */
    public $timeout;

    /**
     * @var bool
     */
    public $utf8;

    /**
     * @var bool
     */
    public $passive;

    /**
     * @var int
     */
    public $transferMode;

    /**
     * @var string `windows` or `unix`
     */
    public $systemType;

    /**
     * @var bool
     */
    public $ignorePassiveAddress;

    /**
     * @var bool
     */
    public $timestampsOnUnixListingsEnabled;

    /**
     * @var bool
     */
    public $recurseManually;

    /**
     * @var bool
     */
    public $useRawListOptions;

    /**
     * @var string
     */
    public $passphrase;

    /**
     * @var string[]
     */
    protected $_availableOptions = [
        'host',
        'root',
        'username',
        'password',
        'port',
        'ssl',
        'timeout',
        'utf8',
        'passive',
        'transferMode',
        'systemType',
        'ignorePassiveAddress',
        'timestampsOnUnixListingsEnabled',
        'recurseManually',
        'useRawListOptions',
    ];

    /**
     * @var FtpConnectionOptions
     */
    protected $_connectionOptions;

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
     * @return FtpAdapter|PathPrefixedAdapter
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

        $this->_connectionOptions = FtpConnectionOptions::fromArray($options);

        $adapter = new FtpAdapter($this->_connectionOptions);
        // for UrlGeneratorAdapterTrait
        $adapter->component = $this;

        if ($this->prefix) {
            $adapter = new PathPrefixedAdapter($adapter, $this->prefix);
        }

        return $adapter;
    }
}