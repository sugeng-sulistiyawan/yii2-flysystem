<?php

namespace diecoding\flysystem;

use diecoding\flysystem\traits\UrlGeneratorTrait;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class FtpComponent
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
     * @var FtpConnectionOptions
     */
    protected $_connectionOptions;

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
        if (empty($this->password)) {
            throw new InvalidConfigException('The "password" property must be set.');
        }

        $this->initEncrypter($this->password, $this->username);

        parent::init();
    }

    /**
     * @return FtpAdapter
     */
    protected function initAdapter()
    {
        $this->root = (string) Yii::getAlias($this->root);
        $this->root = $this->normalizePath($this->root . '/' . $this->prefix);

        $options = [];
        foreach ([
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
        ] as $property) {
            $options[$property] = $this->$property;
        }

        $this->_connectionOptions = FtpConnectionOptions::fromArray($options);

        return new FtpAdapter($this->_connectionOptions);
    }
}
