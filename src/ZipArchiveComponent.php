<?php

namespace diecoding\flysystem;

use diecoding\flysystem\traits\UrlGeneratorTrait;
use League\Flysystem\ChecksumAlgoIsNotSupported;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\Config;
use League\Flysystem\UnableToGeneratePublicUrl;
use League\Flysystem\UnableToGenerateTemporaryUrl;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Interacting with an ZipArchive filesystem
 * ! Notice
 * It's important to know this adapter does not fully comply with the adapter contract. The difference(s) is/are:
 * - Checksum setting or retrieving is not supported.
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
class ZipArchiveComponent extends AbstractComponent implements PublicUrlGenerator, TemporaryUrlGenerator, ChecksumProvider
{
    use UrlGeneratorTrait;

    /**
     * @var string
     */
    public $pathToZip;

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

        return new ZipArchiveAdapter(
            new FilesystemZipArchiveProvider($this->pathToZip),
            (string) $this->prefix
        );
    }

    public function checksum(string $path, Config $config): string
    {
        if ($this->debug) {
            throw new ChecksumAlgoIsNotSupported('ZipArchiveComponent does not support this operation.');
        }

        return '';
    }
}