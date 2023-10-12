<?php

namespace diecoding\flysystem;

use diecoding\flysystem\traits\UrlGeneratorTrait;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Interacting with the local filesystem
 * @see https://flysystem.thephpleague.com/docs/adapter/local/
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\LocalComponent::class,
 *         'path' => dirname(dirname(__DIR__)) . '/storage', // or you can use @alias
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
class LocalComponent extends AbstractComponent implements PublicUrlGenerator, TemporaryUrlGenerator
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

        $this->initEncrypter($this->secret);

        parent::init();
    }

    /**
     * @return LocalFilesystemAdapter|PathPrefixedAdapter
     */
    protected function initAdapter()
    {
        $this->path = (string) Yii::getAlias($this->path);

        $adapter = new LocalFilesystemAdapter($this->path);
        if ($this->prefix) {
            $adapter = new PathPrefixedAdapter($adapter, $this->prefix);
        }

        return $adapter;
    }
}