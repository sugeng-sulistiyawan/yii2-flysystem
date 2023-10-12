<?php

namespace diecoding\flysystem;

use diecoding\flysystem\traits\UrlGeneratorTrait;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Interacting with an ftp filesystem
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
 *         'port' => 21,
 *         'ssl' => false,
 *         'timeout' => 90,
 *         'utf8' => false,
 *         'passive' => true,
 *         'transferMode' => FTP_BINARY,
 *         'systemType' => null, // 'windows' or 'unix'
 *         'ignorePassiveAddress' => null, // true or false
 *         'timestampsOnUnixListingsEnabled' => false,
 *         'recurseManually' => true,
 *         'useRawListOptions' => null, // true or false
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
class FtpComponent extends AbstractComponent
{
    use UrlGeneratorTrait;

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
    public $port = 21;

    /**
     * @var bool
     */
    public $ssl = false;

    /**
     * @var int
     */
    public $timeout = 90;

    /**
     * @var bool
     */
    public $utf8 = false;

    /**
     * @var bool
     */
    public $passive = true;

    /**
     * @var int
     */
    public $transferMode = FTP_BINARY;

    /**
     * @var string|null `windows` or `unix`
     */
    public $systemType = null;

    /**
     * @var bool|null
     */
    public $ignorePassiveAddress = null;

    /**
     * @var bool
     */
    public $timestampsOnUnixListingsEnabled = false;

    /**
     * @var bool
     */
    public $recurseManually = true;

    /**
     * @var bool|null
     */
    public $useRawListOptions = null;

    /**
     * @var string|null
     */
    public $passphrase = null;

    /**
     * @var array
     */
    protected $_properties = [
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
        foreach ($this->_properties as $property) {
            $options[$property] = $this->$property;
        }

        $this->_connectionOptions = FtpConnectionOptions::fromArray($options);

        $adapter = new FtpAdapter($this->_connectionOptions);
        if ($this->prefix) {
            $adapter = new PathPrefixedAdapter($adapter, $this->prefix);
        }

        return $adapter;
    }
}