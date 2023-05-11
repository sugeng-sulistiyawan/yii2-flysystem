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
     * @var string
     */
    private $_passphrase;

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
        $encryptedString = Yii::$app->getSecurity()->encryptByPassword($string, $this->_passphrase);
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
        $decodedString = (string) Yii::$app->getSecurity()->decryptByPassword($decodedString, $this->_passphrase);

        return $decodedString;
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
    }
}
