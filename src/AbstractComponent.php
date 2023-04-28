<?php

namespace diecoding\flysystem;

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use yii\base\Component;
use yii\helpers\FileHelper;

/**
 * Class AbstractComponent
 *
 * @package diecoding\flysystem
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
 * @method \League\Flysystem\DirectoryListing listContents(string $location, bool $deep = false)
 * @method void move(string $source, string $destination, array $config = [])
 * @method void copy(string $source, string $destination, array $config = [])
 * @method int lastModified(string $path)
 * @method int fileSize(string $path)
 * @method string mimeType(string $path)
 * @method void setVisibility(string $path, string $visibility)
 * @method string visibility(string $path)
 * @method string publicUrl(string $path, array $config = [])
 * @method string temporaryUrl(string $path, DateTimeInterface $expiresAt, array $config = [])
 * @method string checksum(string $path, array $config = [])
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
abstract class AbstractComponent extends Component
{
    /**
     * @var Config|array|string|null
     */
    public $config;

    /** 
     * @var string|null 
     */
    public $prefix;

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
        $adapter          = $this->initAdapter();
        $this->config     = $this->config ?? [];
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
     * @return FilesystemAdapter $adapter
     */
    abstract protected function initAdapter();

    /**
     * Normalizes a file/directory path.
     *
     * The normalization does the following work:
     *
     * - Convert all directory separators into `DIRECTORY_SEPARATOR` (e.g. "\a/b\c" becomes "a/b/c")
     * - Remove trailing directory separators (e.g. "/a/b/c/" becomes "a/b/c")
     * - Remove first directory separators (e.g. "/a/b/c" becomes "a/b/c")
     * - Turn multiple consecutive slashes into a single one (e.g. "/a///b/c" becomes "a/b/c")
     * - Remove ".." and "." based on their meanings (e.g. "/a/./b/../c" becomes "a/c")
     *
     * Note: For registered stream wrappers, the consecutive slashes rule
     * and ".."/"." translations are skipped.
     * 
     * @param string $path
     * @return string
     */
    public function normalizePath(string $path)
    {
        $prefix = $this->prefix ? "{$this->prefix}/" : '';
        $path   = FileHelper::normalizePath($prefix . $path, "/");

        return $path[0] === "/" ? substr($path, 1) : $path;
    }
}
