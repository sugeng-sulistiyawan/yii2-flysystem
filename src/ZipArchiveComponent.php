<?php

namespace diecoding\flysystem;

use League\Flysystem\ChecksumAlgoIsNotSupported;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\Config;
use League\Flysystem\UnableToGeneratePublicUrl;
use League\Flysystem\UnableToGenerateTemporaryUrl;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use League\Flysystem\WebDAV\WebDAVAdapter;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Interacting with an ZipArchive filesystem
 * ! Notice
 * It's important to know this adapter does not fully comply with the adapter contract. The difference(s) is/are:
 * - Checksum setting or retrieving is not supported.
 * - PublicUrl setting or retrieving is not supported.
 * - TemporaryUrl setting or retrieving is not supported.
 * @see https://flysystem.thephpleague.com/docs/adapter/zip-archive/
 * 
 * ```php
 * 'components' => [
 *     'fs' => [
 *         'class' => \diecoding\flysystem\ZipArchiveComponent::class,
 *         'pathToZip' => dirname(dirname(__DIR__)) . '/storage.zip', // or you can use @alias
 *         'prefix' => '', // root directory inside zip file
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

        parent::init();
    }

    /**
     * @return WebDAVAdapter
     */
    protected function initAdapter()
    {
        $this->pathToZip = (string) Yii::getAlias($this->pathToZip);

        return new ZipArchiveAdapter(
            new FilesystemZipArchiveProvider($this->pathToZip),
            $this->prefix
        );
    }

    public function publicUrl(string $path, Config $config): string
    {
        if ($this->debug) {
            throw new UnableToGeneratePublicUrl('ZipArchiveComponent does not support this operation.', $path);
        }

        return '';
    }

    public function temporaryUrl(string $path, \DateTimeInterface $expiresAt, Config $config): string
    {
        if ($this->debug) {
            throw new UnableToGenerateTemporaryUrl('ZipArchiveComponent does not support this operation.', $path);
        }

        return '';
    }

    public function checksum(string $path, Config $config): string
    {
        if ($this->debug) {
            throw new ChecksumAlgoIsNotSupported('ZipArchiveComponent does not support this operation.');
        }

        return '';
    }
}