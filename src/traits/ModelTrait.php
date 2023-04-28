<?php

namespace diecoding\flysystem\traits;

use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
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
     * @return \diecoding\flysystem\AbstractComponent
     */
    public function getFsComponent()
    {
        return Yii::$app->get('fs');
    }

    /**
     * Save UploadedFile to AWS S3.
     * ! Important: This function uploads this model filename to keep consistency of the model.
     * 
     * @param UploadedFile $file Uploaded file to save
     * @param string $attribute Attribute name where the uploaded filename name will be saved
     * @param string $fileName Name which file will be saved. If empty will use the name from $file
     * @param bool $autoExtension `true` to automatically append or replace the extension to the file name. Default is `true`
     * @param array $config
     * 
     * @return bool `true` for Uploaded full path of filename on success or `false` in failure.
     */
    public function saveUploadedFile(UploadedFile $file, $attribute, $fileName = '', $autoExtension = true, $config = [])
    {
        if ($this->hasError) {
            return false;
        }
        if (empty($fileName)) {
            $fileName = $file->name;
        }
        if ($autoExtension) {
            $_file = (string) pathinfo($fileName, PATHINFO_FILENAME);
            $fileName = $_file . '.' . $file->extension;
        }
        $filePath = $this->getAttributePath($attribute) . $fileName;
        try {
            $localPath = $file->tempName;
            $handle    = fopen($localPath, 'r');
            $contents  = fread($handle, filesize($localPath));
            fclose($handle);
            
            $filesystem = $this->getFsComponent();
            $filesystem->write($filesystem->normalizePath($filePath), $contents, $config);

            return true;
        } catch (UnableToWriteFile $exception) {

            Yii::error([
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Delete model file attribute from AWS S3.
     * 
     * @param string $attribute Attribute name which holds the filename
     * 
     * @return bool `true` on success or if file doesn't exist.
     */
    public function removeFile($attribute)
    {
        if (empty($this->{$attribute})) {
            return true;
        }
        $filePath = $this->getAttributePath($attribute) . $this->{$attribute};
        try {
            $filesystem = $this->getFsComponent();
            $filesystem->delete($this->normalizePath($filePath));

            $this->{$attribute} = null;

            return true;
        } catch (UnableToDeleteFile $exception) {

            Yii::error([
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return false;
        }
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

        return $this->getFsComponent()->getUrl($this->getAttributePath($attribute) . $this->{$attribute});
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

        return $this->getFsComponent()->getPresignedUrl(
            $this->getAttributePath($attribute) . $this->{$attribute},
            $this->getPresignedUrlDuration($attribute)
        );
    }

    /**
     * Retrieves the URL signature expiration.
     * 
     * @param string $attribute Attribute name which holds the duration
     * 
     * @return int|string|\DateTimeInterface URL expiration
     */
    public function getPresignedUrlDuration($attribute)
    {
        if (empty($this->{$attribute})) {
            return 0;
        }

        return '+30 minutes';
    }

    /**
     * List the paths on AWS S3 to each model file attribute.
     * It must be a Key-Value array, where Key is the attribute name and Value is the base path for the file in S3.
     * Override this method for saving each attribute in its own "folder".
     * 
     * @return array Key-Value of attributes and its paths.
     */
    public function attributePaths()
    {
        return [];
    }

    /**
     * Retrieves the base path on AWS S3 for a given attribute.
     * @see attributePaths()
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
