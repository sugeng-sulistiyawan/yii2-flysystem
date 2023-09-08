<?php

namespace diecoding\flysystem\traits;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\StringHelper;

/**
 * Trait EncrypterTrait for Model
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
trait EncrypterTrait
{
    /**
     * @var string The cipher to use for encryption and decryption.
     */
    public $cipher = 'AES-128-CBC';
    /**
     * @var array[] Look-up table of block sizes and key sizes for each supported OpenSSL cipher.
     *
     * In each element, the key is one of the ciphers supported by OpenSSL (@see openssl_get_cipher_methods()).
     * The value is an array of two integers, the first is the cipher's block size in bytes and the second is
     * the key size in bytes.
     *
     * > Warning: All OpenSSL ciphers that we recommend are in the default value, i.e. AES in CBC mode.
     *
     * > Note: Yii's encryption protocol uses the same size for cipher key, HMAC signature key and key
     * derivation salt.
     */
    public $allowedCiphers = [
        'AES-128-CBC' => [16, 16],
        'AES-192-CBC' => [16, 24],
        'AES-256-CBC' => [16, 32],
    ];

    /**
     * @var string
     */
    private $_passphrase;

    /**
     * @var string
     */
    private $_iv;

    /**
     * Init Encrypter
     * 
     * @param string $passphrase
     * @param string $iv
     * 
     * @return void
     * @throws InvalidConfigException
     */
    public function initEncrypter($passphrase)
    {
        if (!extension_loaded('openssl')) {
            throw new InvalidConfigException('Encryption requires the OpenSSL PHP extension');
        }
        if (!isset($this->allowedCiphers[$this->cipher][0], $this->allowedCiphers[$this->cipher][1])) {
            throw new InvalidConfigException($this->cipher . ' is not an allowed cipher');
        }

        $this->normalizePassphrase($passphrase);
    }

    /**
     * Encrypts a string.
     * 
     * @param string $string the string to encrypt
     * @return string the encrypted string
     */
    public function encrypt($string)
    {
        $encrypted = openssl_encrypt($string, $this->cipher, $this->_passphrase, OPENSSL_RAW_DATA, $this->_iv);
        if ($encrypted === false) {
            throw new \yii\base\Exception('OpenSSL failure on encryption: ' . openssl_error_string());
        }

        return StringHelper::base64UrlEncode($encrypted);
    }

    /**
     * Decrypts a string. 
     * False is returned in case it was not possible to decrypt it.
     * 
     * @param string $string the string to decrypt
     * @return string|bool the decrypted string or false on authentication failure
     */
    public function decrypt($string)
    {
        $encrypted = StringHelper::base64UrlDecode($string);
        $decrypted = openssl_decrypt($encrypted, $this->cipher, $this->_passphrase, OPENSSL_RAW_DATA, $this->_iv);
        if ($decrypted === false) {
            throw new \yii\base\Exception('OpenSSL failure on decryption: ' . openssl_error_string());
        }

        return $decrypted;
    }

    /**
     * @param string $passphrase
     * @param int $minPassphraseLength
     * 
     * @return void
     * @throws InvalidConfigException
     */
    private function normalizePassphrase($passphrase)
    {
        $this->_passphrase = $passphrase;
        if (empty($this->_passphrase)) {
            throw new InvalidConfigException('The "passphrase" property must be set.');
        }

        $minPassphraseLength = 32;
        $passphraseLength    = strlen($this->_passphrase);
        if ($passphraseLength < $minPassphraseLength) {
            $this->_passphrase = str_repeat($this->_passphrase, (int) ceil($minPassphraseLength / $passphraseLength));
        }

        list($blockSize, $keySize) = $this->allowedCiphers[$this->cipher];
        $this->_iv                 = Yii::$app->id . Yii::$app->name;
        $ivLength                  = strlen($this->_iv);
        if ($ivLength < $blockSize) {
            $this->_iv = str_repeat($this->_iv, (int) ceil($blockSize / $ivLength));
        }
        $this->_iv = substr($this->_iv, 0, $blockSize);
    }
}
