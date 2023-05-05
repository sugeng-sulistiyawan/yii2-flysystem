<?php

namespace diecoding\flysystem;

use DateTimeInterface;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * Class LocalComponent
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\LocalComponent::class,
 *         'path' => dirname(dirname(__DIR__)) . '/storage', // or you can use @alias
 *         'key' => 'my-key',
 *         'secret' => 'my-secret', 
 *         'action' => '/site/file',
 *         'prefix' => '',
 *     ],
 * ],
 * ```
 * 
 * @package diecoding\flysystem
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
class LocalComponent extends AbstractComponent
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $secret;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $action = '/site/file';

    /**
     * @var string
     */
    public $cipherAlgo = 'aes-128-cbc';

    /**
     * @var string
     */
    protected $_basePath;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->path)) {
            throw new InvalidConfigException('The "path" property must be set.');
        }

        if (empty($this->key)) {
            throw new InvalidConfigException('The "key" property must be set.');
        }

        if (empty($this->secret)) {
            throw new InvalidConfigException('The "secret" property must be set.');
        }

        $ivLength     = strlen($this->key);
        $mustIvLength = openssl_cipher_iv_length($this->cipherAlgo);
        if ($ivLength !== $mustIvLength) {
            throw new InvalidConfigException('The "key" should be exactly ' . $mustIvLength . ' bytes long, ' . $ivLength . ' given.');
        }

        parent::init();
    }

    public function publicUrl(string $path, array $config = []): string
    {
        $params = [
            'path'    => $this->normalizePath($path),
            'expires' => 0,
            'config'  => $config,
        ];

        return Url::toRoute([$this->action, 'data' => $this->encrypt(Json::encode($params))], true);
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, array $config = []): string
    {
        $params = [
            'path'    => $this->normalizePath($path),
            'expires' => (int) $expiresAt->getTimestamp(),
            'config'  => $config,
        ];

        return Url::toRoute([$this->action, 'data' => $this->encrypt(Json::encode($params))], true);
    }

    /**
     * Encrypts a string.
     * 
     * @param string $string the string to encrypt
     * @return string the encrypted string
     */
    public function encrypt($string)
    {
        $encryptedString = openssl_encrypt($string, $this->cipherAlgo, $this->secret, OPENSSL_RAW_DATA, $this->key);
        $encryptedString = StringHelper::base64UrlEncode($encryptedString);

        return $encryptedString;
    }

    /**
     * Decrypts a string. 
     * False is returned in case it was not possible to decrypt it.
     * 
     * @param string $string the string to decrypt
     * @return string the decrypted string
     */
    public function decrypt($string)
    {
        $decodedString = StringHelper::base64UrlDecode($string);
        $decodedString = openssl_decrypt($decodedString, $this->cipherAlgo, $this->secret, OPENSSL_RAW_DATA, $this->key);

        return $decodedString;
    }

    /**
     * @return LocalFilesystemAdapter
     */
    protected function initAdapter()
    {
        $this->path      = (string) Yii::getAlias($this->path);
        $this->_basePath = $this->normalizePath($this->path . '/' . $this->prefix);

        return new LocalFilesystemAdapter($this->_basePath);
    }
}
