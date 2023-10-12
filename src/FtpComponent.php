<?php

namespace diecoding\flysystem;

use diecoding\flysystem\traits\UrlGeneratorTrait;
use League\Flysystem\ChecksumAlgoIsNotSupported;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\Config;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use Yii;

/**
 * Interacting with an FTP filesystem
 * ! Notice
 * It's important to know this adapter does not fully comply with the adapter contract. The difference(s) is/are:
 * - Checksum setting or retrieving is not supported.
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
class FtpComponent extends AbstractComponent implements PublicUrlGenerator, TemporaryUrlGenerator, ChecksumProvider
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
     * @var array
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
        if ($this->prefix) {
            $adapter = new PathPrefixedAdapter($adapter, $this->prefix);
        }

        return $adapter;
    }

    public function checksum(string $path, Config $config): string
    {
        if ($this->debug) {
            throw new ChecksumAlgoIsNotSupported('FtpComponent does not support this operation.');
        }

        return '';
    }
}