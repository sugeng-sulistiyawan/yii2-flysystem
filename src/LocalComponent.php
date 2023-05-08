<?php

namespace diecoding\flysystem;

use diecoding\flysystem\traits\UrlGeneratorTrait;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

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
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
class LocalComponent extends AbstractComponent
{
    use UrlGeneratorTrait;

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
    protected $_location;

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
        if (empty($this->key)) {
            throw new InvalidConfigException('The "key" property must be set.');
        }

        $this->initEncrypter($this->secret, $this->key);

        parent::init();
    }

    /**
     * @return LocalFilesystemAdapter
     */
    protected function initAdapter()
    {
        $this->path      = (string) Yii::getAlias($this->path);
        $this->_location = FileHelper::normalizePath($this->path . '/' . $this->prefix, '/');

        return new LocalFilesystemAdapter($this->_location);
    }
}
