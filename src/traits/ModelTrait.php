<?php

namespace diecoding\aws\s3\traits;

use Yii;
use yii\web\UploadedFile;

/**
 * Trait ModelTrait for Model
 * 
 * @package diecoding\aws\s3\traits
 */
trait ModelTrait
{
    /**
     * @return \diecoding\aws\s3\Service
     */
    public function getS3Component()
    {
        return Yii::$app->get('s3');
    }

    /**
     * Save UploadedFile to AWS S3.
     * ! Important: This function uploads this model filename to keep consistency of the model.
     * 
     * @param UploadedFile $file Uploaded file to save
     * @param string $attribute Attribute name where the uploaded filename name will be saved
     * @param string $fileName Name which file will be saved. If empty will use the name from $file
     * @param bool $autoExtension `true` to automatically append or replace the extension to the file name. Default is `true`
     * 
     * @return string|false Uploaded full path of filename on success or `false` in failure.
     */
    public function saveUploadedFile(UploadedFile $file, $attribute, $fileName = '', $autoExtension = true)
    {
        if ($this->hasError && !$file instanceof UploadedFile) {
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

        /** @var \Aws\ResultInterface $result */
        $result = $this->getS3Component()
            ->commands()
            ->upload(
                $filePath,
                $file->tempName
            )
            ->withContentType($file->type)
            ->execute();

        // Validate successful upload to S3
        if ($this->isSuccessResponseStatus($result)) {
            $this->{$attribute} = $fileName;

            return $fileName;
        }

        return false;
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
            // No file to remove
            return true;
        }

        $filePath = $this->getAttributePath($attribute) . $this->{$attribute};
        $result = $this->getS3Component()
            ->commands()
            ->delete($filePath)
            ->execute();

        // Validate successful removal from S3
        if ($this->isSuccessResponseStatus($result)) {
            $this->{$attribute} = null;

            return true;
        }

        return false;
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

        return $this->getS3Component()->getUrl($this->getAttributePath($attribute) . $this->{$attribute});
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

        return $this->getS3Component()->getPresignedUrl(
            $this->getAttributePath($attribute) . $this->{$attribute},
            $this->getPresignedUrlDuration($attribute)
        );
    }

    /**
     * Retrieves the URL signature expiration.
     * 
     * @param string $attribute Attribute name which holds the duration
     * 
     * @return mixed URL expiration
     */
    public function getPresignedUrlDuration($attribute)
    {
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

    /**
     * Check for valid status code from the AWS S3 response.
     * Success responses will be considered status codes is 2**.
     * Override function for custom validations.
     * 
     * @param \Aws\ResultInterface $response AWS S3 response containing the status code
     * 
     * @return bool whether this response is successful.
     */
    public function isSuccessResponseStatus($response)
    {
        return !empty($response->get('@metadata')['statusCode']) &&
            $response->get('@metadata')['statusCode'] >= 200 &&
            $response->get('@metadata')['statusCode'] < 300;
    }
}
