<?php

namespace diecoding\flysystem\traits;

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

        $this->normalizePassphrase();
        $this->normalizeIv();
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
     * @param int $minPassphraseLength
     * @return void
     */
    private function normalizePassphrase($minPassphraseLength = 32)
    {
        $passphraseLength = strlen($this->_passphrase);
        if ($passphraseLength < $minPassphraseLength) {
            $this->_passphrase = str_repeat($this->_passphrase, (int) ceil($minPassphraseLength / $passphraseLength));
        }
    }

    /**
     * @return void
     */
    private function normalizeIv()
    {
        $ivLength     = strlen($this->_iv);
        $mustIvLength = openssl_cipher_iv_length($this->_cipherAlgo);
        if ($ivLength < $mustIvLength) {
            $this->_iv = str_repeat($this->_iv, (int) ceil($mustIvLength / $ivLength));
        }

        $this->_iv = substr($this->_iv, 0, $mustIvLength);
    }
}
