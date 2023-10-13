<?php

namespace diecoding\flysystem;

use diecoding\flysystem\adapter\ZipArchiveAdapter;
use diecoding\flysystem\traits\UrlGeneratorComponentTrait;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Interacting with an ZipArchive filesystem
 * @see https://flysystem.thephpleague.com/docs/adapter/zip-archive/
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\ZipArchiveComponent::class,
 *         'pathToZip' => dirname(dirname(__DIR__)) . '/storage.zip', // or you can use @alias
 *         'secret' => 'my-secret', // for secure route url
 *         // 'action' => '/site/file', // action route
 *         // 'prefix' => '', // root directory inside zip file
 *     ],
 * ],
 * ```
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
class ZipArchiveComponent extends AbstractComponent
{
    use UrlGeneratorComponentTrait;

    /**
     * @var string
     */
    public $pathToZip;

    /**
     * @var string
     */
    public $secret;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->pathToZip)) {
            throw new InvalidConfigException('The "pathToZip" property must be set.');
        }
        if (empty($this->secret)) {
            throw new InvalidConfigException('The "secret" property must be set.');
        }

        $this->initEncrypter($this->secret);

        parent::init();
    }

    /**
     * @return ZipArchiveAdapter
     */
    protected function initAdapter()
    {
        $this->pathToZip = (string) Yii::getAlias($this->pathToZip);

        $adapter = new ZipArchiveAdapter(
            new FilesystemZipArchiveProvider($this->pathToZip),
            (string) $this->prefix
        );
        // for UrlGeneratorAdapterTrait
        $adapter->component = $this;

        return $adapter;
    }
}