<?php

namespace diecoding\flysystem\traits;

use Yii;
use yii\web\UploadedFile;

/**
 * Trait ModelTrait for Model
 * 
 * @package diecoding\flysystem\traits
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
trait ModelTrait
{
    /**
     * @return object|null|\diecoding\flysystem\AbstractComponent
     */
    public function getFsComponent()
    {
        return Yii::$app->get('fs');
    }

    /**
     * List the paths on filesystem to each model file attribute.
     * It must be a Key-Value array, where Key is the attribute name and Value is the base path for the file in filesystem.
     * Override this method for saving each attribute in its own "folder".
     * 
     * @return array Key-Value of attributes and its paths.
     */
    public function attributePaths()
    {
        return [];
    }

    /**
     * Save UploadedFile.
     * ! Important: This function uploads this model filename to keep consistency of the model.
     * 
     * @param UploadedFile $file Uploaded file to save
     * @param string $attribute Attribute name where the uploaded filename name will be saved
     * @param string $fileName Name which file will be saved. If empty will use the name from $file
     * @param bool $autoExtension `true` to automatically append or replace the extension to the file name. Default is `true`
     * 
     * @return void
     */
    public function saveUploadedFile(UploadedFile $file, $attribute, $fileName = '', $autoExtension = true)
    {
        if ($this->hasError) {
            return;
        }
        if (empty($fileName)) {
            $fileName = $file->name;
        }
        if ($autoExtension) {
            $_file    = (string) pathinfo($fileName, PATHINFO_FILENAME);
            $fileName = $_file . '.' . $file->extension;
        }
        $filePath  = $this->getAttributePath($attribute) . '/' . $fileName;
        $localPath = $file->tempName;
        $handle    = fopen($localPath, 'r');
        $contents  = fread($handle, filesize($localPath));
        fclose($handle);

        $filesystem = $this->getFsComponent();
        $filesystem->write($filesystem->normalizePath($filePath), $contents);

        $this->{$attribute} = $fileName;
    }

    /**
     * Delete model file attribute.
     * 
     * @param string $attribute Attribute name which holds the filename
     * 
     * @return void
     */
    public function removeFile($attribute)
    {
        if (empty($this->{$attribute})) {
            return;
        }
        $filePath   = $this->getAttributePath($attribute) . '/' . $this->{$attribute};
        $filesystem = $this->getFsComponent();
        $filesystem->delete($filesystem->normalizePath($filePath));

        $this->{$attribute} = null;
    }

    /**
     * Retrieves the URL for a given model file attribute.
     * 
     * @param string $attribute Attribute name which holds the filename
     * 
     * @return string URL to access file
     */
    public function getFileUrl($attribute)
    {
        if (empty($this->{$attribute})) {
            return '';
        }
        $filePath   = $this->getAttributePath($attribute) . '/' . $this->{$attribute};
        $filesystem = $this->getFsComponent();

        return $filesystem->publicUrl($filesystem->normalizePath($filePath));
    }

    /**
     * Retrieves the presigned URL for a given model file attribute.
     * 
     * @param string $attribute Attribute name which holds the filename
     * 
     * @return string Presigned URL to access file
     */
    public function getFilePresignedUrl($attribute)
    {
        if (empty($this->{$attribute})) {
            return '';
        }
        $filePath   = $this->getAttributePath($attribute) . '/' . $this->{$attribute};
        $filesystem = $this->getFsComponent();

        return $filesystem->temporaryUrl($filesystem->normalizePath($filePath), $this->getPresignedUrlDuration($attribute));
    }

    /**
     * Retrieves the URL signature expiration.
     * 
     * @param string $attribute Attribute name which holds the duration
     * 
     * @return \DateTimeInterface URL expiration
     */
    public function getPresignedUrlDuration($attribute)
    {
        $filesystem = $this->getFsComponent();

        if (empty($this->{$attribute})) {
            return $filesystem->convertToDateTime('now');
        }

        return $filesystem->convertToDateTime('+5 Minutes');
    }

    /**
     * Retrieves the base path on filesystem for a given attribute.
     * see `attributePaths()`
     * 
     * @param string $attribute Attribute to get its path
     * 
     * @return string The path where all file of that attribute should be stored. Returns empty string if the attribute isn't in the list.
     */
    public function getAttributePath($attribute)
    {
        $paths = $this->attributePaths();
        if (array_key_exists($attribute, $paths)) {
            return $paths[$attribute];
        }

        return '';
    }
}
