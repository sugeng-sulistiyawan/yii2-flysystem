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
 *         'basePath' => '', // for multiple project in single storage, will be format to `$basePath . $path`
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
        if ($this->path === null) {
            throw new InvalidConfigException('The "path" property must be set.');
        }

        $this->path = $this->normalizePath(Yii::getAlias($this->path));

        parent::init();
    }

    /**
     * @return LocalFilesystemAdapter
     */
    protected function initAdapter()
    {
        return new LocalFilesystemAdapter($this->path);
    }
}
