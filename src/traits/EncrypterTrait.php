<?php

namespace diecoding\flysystem\traits;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\StringHelper;

/**
 * Trait EncrypterTrait for Model
 * 
 * @package diecoding\flysystem\traits
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
trait EncrypterTrait
{
    /**
     * @var string
     */
    private $_cipherAlgo;

    /**
     * @var string
     */
    private $_passphrase;

    /**
     * @var string
     */
    private $_iv;

    /**
     * Set Encrypter
     * 
     * @return void
     * @throws InvalidConfigException
     */
    public function setEncrypter($passphrase, $iv, $cipherAlgo = 'aes-128-cbc')
    {
        $this->_passphrase = $passphrase;
        $this->_iv         = $iv;
        $this->_cipherAlgo = $cipherAlgo;

        if (empty($this->_passphrase)) {
            throw new InvalidConfigException('The "passphrase" property must be set.');
        }
        if (empty($this->_iv)) {
            throw new InvalidConfigException('The "iv" property must be set.');
        }

        $this->validateIvLength();
    }

    /**
     * Encrypts a string.
     * 
     * @param string $string the string to encrypt
     * @return string the encrypted string
     */
    public function encrypt($string)
    {
        $encryptedString = openssl_encrypt($string, $this->_cipherAlgo, $this->_passphrase, OPENSSL_RAW_DATA, $this->_iv);
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
        $decodedString = openssl_decrypt($decodedString, $this->_cipherAlgo, $this->_passphrase, OPENSSL_RAW_DATA, $this->_iv);

        return $decodedString;
    }

    /**
     * Validate IV Length
     * 
     * @return void
     * @throws InvalidConfigException
     */
    private function validateIvLength()
    {
        $ivLength     = strlen($this->_iv);
        $mustIvLength = openssl_cipher_iv_length($this->_cipherAlgo);
        if ($ivLength !== $mustIvLength) {
            throw new InvalidConfigException('The "iv" should be exactly ' . $mustIvLength . ' bytes long, ' . $ivLength . ' given.');
        }
    }
}