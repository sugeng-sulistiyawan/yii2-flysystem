<?php

declare(strict_types=1);

namespace diecoding\flysystem\adapter;

use DateTimeInterface;
use diecoding\flysystem\traits\ChecksumAdapterTrait;
use diecoding\flysystem\traits\UrlGeneratorAdapterTrait;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\Config;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use League\Flysystem\ZipArchive\ZipArchiveAdapter as LeagueZipArchiveAdapter;
use League\Flysystem\ZipArchive\ZipArchiveProvider;
use League\MimeTypeDetection\MimeTypeDetector;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @method bool fileExists(string $location)
 * @method bool directoryExists(string $location)
 * @method bool has(string $location) check fileExists or directoryExists
 * @method void write(string $location, string $contents, array $config = [])
 * @method void writeStream(string $location, $contents, array $config = [])
 * @method string read(string $location)
 * @method resource readStream(string $location)
 * @method void delete(string $location)
 * @method void deleteDirectory(string $location)
 * @method void createDirectory(string $location, array $config = [])
 * @method \League\Flysystem\DirectoryListing listContents(string $location, bool = \League\Flysystem\Filesystem::LIST_SHALLOW)
 * @method void move(string $source, string $destination, array $config = [])
 * @method void copy(string $source, string $destination, array $config = [])
 * @method int lastModified(string $path)
 * @method int fileSize(string $path)
 * @method string mimeType(string $path)
 * @method void setVisibility(string $path, string $visibility)
 * @method string visibility(string $path)
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2024
 */
final class ZipArchiveAdapter implements ChecksumProvider, PublicUrlGenerator, TemporaryUrlGenerator
{
    use UrlGeneratorAdapterTrait, ChecksumAdapterTrait;

    /**
     * @var LeagueZipArchiveAdapter
     */
    protected $_adapter;

    public function __construct(
        private ZipArchiveProvider $zipArchiveProvider,
        string $root = '',
        ?MimeTypeDetector $mimeTypeDetector = null,
        ?VisibilityConverter $visibility = null,
        private bool $detectMimeTypeUsingPath = false,
    ) {
        $this->setAdapter(new LeagueZipArchiveAdapter($zipArchiveProvider, $root, $mimeTypeDetector, $visibility, $detectMimeTypeUsingPath));
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->getAdapter(), $method], $parameters);
    }

    /**
     * @return LeagueZipArchiveAdapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * @param LeagueZipArchiveAdapter $value
     * @return void
     */
    public function setAdapter(LeagueZipArchiveAdapter $value)
    {
        $this->_adapter = $value;
    }

    public function publicUrl(string $path, Config $config): string
    {
        // TODO: Use absolute path and don't encrypt
        $params = [
            'path' => $path,
            'expires' => 0,
        ];

        return Url::toRoute([$this->component?->action, 'data' => $this->component?->encrypt(Json::encode($params))], true);
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, Config $config): string
    {
        // TODO: Use absolute path and don't encrypt
        $params = [
            'path' => $path,
            'expires' => (int) $expiresAt->getTimestamp(),
        ];

        return Url::toRoute([$this->component?->action, 'data' => $this->component?->encrypt(Json::encode($params))], true);
    }
}
