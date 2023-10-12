<?php

namespace diecoding\flysystem;

use League\Flysystem\ChecksumAlgoIsNotSupported;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\Config;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;
use yii\base\InvalidConfigException;

/**
 * Interacting with an webdav filesystem
 * ! Notice
 * It's important to know this adapter does not fully comply with the adapter contract. The difference(s) is/are:
 * - Visibility setting or retrieving is not supported.
 * - Checksum setting or retrieving is not supported.
 * - TemporaryUrl setting or retrieving equal to PublicUrl.
 * @see https://flysystem.thephpleague.com/docs/adapter/webdav/
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\WebDavComponent::class,
 *         'baseUri' => 'http://your-webdav-server.org/',
 *         'userName' => 'your_user',
 *         'password' => 'superSecret1234',
 *         'prefix' => '',
 *         // 'proxy' => '',
 *         // 'authType' => \Sabre\DAV\Client::AUTH_BASIC,
 *         // 'encoding' => \Sabre\DAV\Client::ENCODING_IDENTITY,
 *     ],
 * ],
 * ```
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
class WebDavComponent extends AbstractComponent implements TemporaryUrlGenerator, ChecksumProvider
{
    /**
     * @var string
     */
    public $baseUri;

    /**
     * @var string
     */
    public $userName;

    /**
     * @var string
     */
    public $password;

    /**
     * @var mixed
     */
    public $proxy;

    /**
     *  authType must be a bitmap, using Client::AUTH_BASIC, Client::AUTH_DIGEST
     *  and Client::AUTH_NTLM. If you know which authentication method will be
     *  used, it's recommended to set it, as it will save a great deal of
     *  requests to 'discover' this information.
     * 
     * @var int
     */
    public $authType;

    /**
     * Encoding is a bitmap with one of the ENCODING constants.
     * 
     * @var int
     */
    public $encoding;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $_availableOptions = [
        'baseUri',
        'userName',
        'password',
        'proxy',
        'authType',
        'encoding',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->baseUri)) {
            throw new InvalidConfigException('The "baseUri" property must be set.');
        }

        parent::init();
    }

    /**
     * @return WebDAVAdapter
     */
    protected function initAdapter()
    {
        $config = [];
        foreach ($this->_availableOptions as $property) {
            if ($this->$property !== null) {
                $config[$property] = $this->$property;
            }
        }

        $this->client = new Client($config);

        return new WebDAVAdapter($this->client, $this->prefix, $this->debug ? WebDAVAdapter::ON_VISIBILITY_THROW_ERROR : WebDAVAdapter::ON_VISIBILITY_IGNORE);
    }

    public function temporaryUrl(string $path, \DateTimeInterface $expiresAt, Config $config): string
    {
        return $this->publicUrl($path, $config);
    }

    public function checksum(string $path, Config $config): string
    {
        if ($this->debug) {
            throw new ChecksumAlgoIsNotSupported('WebDavComponent does not support this operation.');
        }

        return '';
    }
}