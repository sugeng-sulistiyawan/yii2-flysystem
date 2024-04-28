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
 * @method string publicUrl(string $path, array $config = [])
 * @method string temporaryUrl(string $path, \DateTimeInterface $expiresAt, array $config = [])
 * @method string checksum(string $path, array $config = [])
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

    private $_filesystem;

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->getFilesystem(), $method], $parameters);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $adapter = $this->initAdapter();
        $this->setFilesystem(new Filesystem($adapter, $this->config));
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->_filesystem;
    }

    /**
     * @param Filesystem $value
     * @return void
     */
    public function setFilesystem(Filesystem $value)
    {
        $this->_filesystem = $value;
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
     * @return FilesystemAdapter
     */
    abstract protected function initAdapter();
}
