<?php

namespace diecoding\flysystem;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class LocalComponent
 *
 * @package diecoding\flysystem
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\LocalComponent::class,
 *         'path' => dirname(dirname(__DIR__)) . '/storage', // or you can use @alias
 *         'basePath' => '', // for multiple project in single storage, will be format to `$basePath . '/' . $path`
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
    /**
     * @var string
     */
    public $path;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->path)) {
            throw new InvalidConfigException('The "path" property must be set.');
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
        throw new InvalidConfigException('Not Implemented');
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
        throw new InvalidConfigException('Not Implemented');
    }

    /**
     * @return LocalFilesystemAdapter
     */
    protected function initAdapter()
    {
        return new LocalFilesystemAdapter($this->path);
    }
}
