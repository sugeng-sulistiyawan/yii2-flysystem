<?php

namespace diecoding\flysystem;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class LocalComponent
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\LocalComponent::class,
 *         'path' => dirname(dirname(__DIR__)) . '/storage', // or you can use @alias
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

        parent::init();
    }

    /**
     * @return LocalFilesystemAdapter
     */
    protected function initAdapter()
    {
        $location = $this->normalizePath(Yii::getAlias($this->path) . '/' . $this->prefix);

        return new LocalFilesystemAdapter($location);
    }
}
