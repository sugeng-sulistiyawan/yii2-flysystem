<?php

namespace diecoding\flysystem;

use DateTimeImmutable;
use DateTimeInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathNormalizer;
use League\Flysystem\WhitespacePathNormalizer;
use yii\base\Component;

/**
 * Class AbstractComponent
 * 
 * @method bool fileExists(string $path)
 * @method bool directoryExists(string $path)
 * @method void write(string $path, string $contents, \League\Flysystem\Config $config)
 * @method void writeStream(string $path, resource $contents, \League\Flysystem\Config $config)
 * @method string read(string $path)
 * @method resource readStream(string $path)
 * @method void delete(string $path)
 * @method void deleteDirectory(string $path)
 * @method void createDirectory(string $path, \League\Flysystem\Config $config)
 * @method void setVisibility(string $path, string $visibility)
 * @method string|\League\Flysystem\FileAttributes visibility(string $path)
 * @method string|\League\Flysystem\FileAttributes mimeType(string $path)
 * @method int|\League\Flysystem\FileAttributes lastModified(string $path)
 * @method int|\League\Flysystem\FileAttributes fileSize(string $path)
 * @method iterable<\League\Flysystem\StorageAttributes>|\League\Flysystem\DirectoryListing<\League\Flysystem\StorageAttributes> listContents(string $path, bool $deep)
 * @method void move(string $source, string $destination, \League\Flysystem\Config $config)
 * @method void copy(string $source, string $destination, \League\Flysystem\Config $config)
 * @method bool has(string $path) check fileExists or directoryExists
 * @method string checksum(string $path, \League\Flysystem\Config $config)
 * 
 * @method string publicUrl(string $path, \League\Flysystem\Config $config)
 * @method string temporaryUrl(string $path, \DateTimeInterface $expiresAt, \League\Flysystem\Config $config)
 * 
 * @method string encrypt(string $string)
 * @method string decrypt(string $string)
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
abstract class AbstractComponent extends Component
{
    /**
     * @var array
     */
    public $config = [];

    /** 
     * @var string 
     */
    public $prefix;

    /** 
     * @var string 
     */
    public $directorySeparator = DIRECTORY_SEPARATOR;

    /**
     * @var bool
     */
    public $debug = false;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->filesystem, $method], $parameters);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $adapter = $this->initAdapter();
        $this->filesystem = new Filesystem($adapter, $this->config);
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Normalizes a file/directory path.
     * 
     * @param string $path
     * @param PathNormalizer|null $pathNormalizer
     * @return string
     */
    public function normalizePath(string $path, PathNormalizer $pathNormalizer = null)
    {
        $pathNormalizer = $pathNormalizer ?: new WhitespacePathNormalizer();

        return $pathNormalizer->normalizePath($path);
    }

    /**
     * Convert Time To DateTimeImmutable
     *
     * @param int|string|DateTimeInterface $dateValue
     * @return DateTimeImmutable
     */
    public function convertToDateTime($dateValue)
    {
        if ($dateValue instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($dateValue);
        }

        return new DateTimeImmutable($dateValue);
    }

    /**
     * @return FilesystemAdapter $adapter
     */
    abstract protected function initAdapter();
}