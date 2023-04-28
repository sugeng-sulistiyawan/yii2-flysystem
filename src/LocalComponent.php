<?php

namespace diecoding\flysystem;

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
 *         'cipherAlgo' => 'aes-128-cbc',
 *         'secret' => 'my-secret',
 *         'action' => '/site/file',
 *         'basePath' => '', // for multiple project in single storage, will be format to `$basePath . '/' . $path`
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
    public $cipherAlgo = 'aes-128-cbc';

    /**
     * @var string
     */
    public $secret;

    /**
     * @var string
     */
    public $action = '/site/file';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->path)) {
            throw new InvalidConfigException('The "path" property must be set.');
        }

        if (empty($this->secret)) {
            throw new InvalidConfigException('The "secret" property must be set.');
        }

        $this->path = $this->normalizePath(Yii::getAlias($this->path));

        parent::init();
    }

    /**
     * Get a URL
     * 
     * @param string $filePath
     * @return string
     */
    public function getUrl(string $filePath)
    {
        $params = [
            'filePath' => $filePath,
            'expires' => null,
        ];

        return Url::toRoute([$this->action, 'data' => $this->encrypt(Json::encode($params))], true);
    }

    /**
     * Get a pre-signed URL
     * 
     * @param string $filePath
     * @param int|string|\DateTimeInterface $expires
     * @return string
     */
    public function getPresignedUrl(string $filePath, $expires = '+5 Minutes')
    {
        $params = [
            'filePath' => $filePath,
            'expires' => $this->convertToTimestamp($expires),
        ];

        return Url::toRoute([$this->action, 'data' => $this->encrypt(Json::encode($params))], true);
    }

    /**
     * @return LocalFilesystemAdapter
     */
    protected function initAdapter()
    {
        return new LocalFilesystemAdapter($this->path);
    }

    /**
     * Encrypts a string.
     * 
     * @param string $string the string to encrypt
     * @return string the encrypted string
     */
    public function encrypt($string)
    {
        $encryptedString = openssl_encrypt($string, $this->cipherAlgo, $this->secret);
        $encryptedString = StringHelper::base64UrlEncode(base64_encode($encryptedString));

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
        $decodedString = base64_decode(StringHelper::base64UrlDecode($string));
        $decodedString = openssl_decrypt($decodedString, $this->cipherAlgo, $this->secret);

        return $decodedString;
    }

    /**
     * Convert To Timestamp
     *
     * @param int|string|\DateTimeInterface $dateValue
     * @param int|string|null $relativeTimeBase
     * @return int|false
     */
    public function convertToTimestamp($dateValue, $relativeTimeBase = null)
    {
        if ($dateValue instanceof \DateTimeInterface) {
            $timestamp = $dateValue->getTimestamp();
        } elseif (!is_numeric($dateValue)) {
            $timestamp = strtotime(
                $dateValue,
                $relativeTimeBase === null ? time() : $relativeTimeBase
            );
        } else {
            $timestamp = $dateValue;
        }

        return $timestamp;
    }
}
