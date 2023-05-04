# Yii2 Flysystem

The League Flysystem for local and remote filesystems library for Yii2.

[![Latest Stable Version](https://img.shields.io/packagist/v/diecoding/yii2-flysystem?label=stable)](https://packagist.org/packages/diecoding/yii2-flysystem)
[![Total Downloads](https://img.shields.io/packagist/dt/diecoding/yii2-flysystem)](https://packagist.org/packages/diecoding/yii2-flysystem)
[![Latest Stable Release Date](https://img.shields.io/github/release-date/sugeng-sulistiyawan/yii2-flysystem)](https://github.com/sugeng-sulistiyawan/yii2-flysystem)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/sugeng-sulistiyawan/yii2-flysystem)](https://scrutinizer-ci.com/g/sugeng-sulistiyawan/yii2-flysystem)
[![Build Status](https://img.shields.io/travis/com/sugeng-sulistiyawan/yii2-flysystem)](https://app.travis-ci.com/sugeng-sulistiyawan/yii2-flysystem)
[![License](https://img.shields.io/github/license/sugeng-sulistiyawan/yii2-flysystem)](https://github.com/sugeng-sulistiyawan/yii2-flysystem)
[![PHP Version Require](https://img.shields.io/packagist/dependency-v/diecoding/yii2-flysystem/php?color=6f73a6)](https://packagist.org/packages/diecoding/yii2-flysystem)

> Yii2 Flysystem uses [league/flysystem](https://github.com/thephpleague/flysystem)

## Table of Contents

- [Yii2 Flysystem](#yii2-flysystem)
  - [Table of Contents](#table-of-contents)
  - [Instalation](#instalation)
  - [Dependencies](#dependencies)
  - [Dev. Dependencies](#dev-dependencies)
  - [Using Traits](#using-traits)
    - [Model Trait](#model-trait)
      - [Using Trait Methods](#using-trait-methods)
      - [Overriding Trait Methods](#overriding-trait-methods)
        - [getFsComponent](#getfscomponent)
        - [attributePaths](#attributepaths)
        - [getPresignedUrlDuration](#getpresignedurlduration)

## Instalation

Package is available on [Packagist](https://packagist.org/packages/diecoding/yii2-flysystem), you can install it using [Composer](https://getcomposer.org).

```shell
composer require diecoding/yii2-flysystem "@dev"
```

or add to the require section of your `composer.json` file.

```shell
"diecoding/yii2-flysystem": "@dev"
```

## Dependencies

- PHP 8.0+
- [yiisoft/yii2](https://github.com/yiisoft/yii2)
- [league/flysystem](https://github.com/thephpleague/flysystem)

## Dev. Dependencies

- [league/flysystem](https://github.com/thephpleague/flysystem)

## Using Traits

### Model Trait

Attach the Trait to the `Model/ActiveRecord` with some media attribute that will be saved in Flysystem (fs):

```php
/**
 * @property string|null $file
 */
class Model extends \yii\db\ActiveRecord
{
    use \diecoding\flysystem\traits\ModelTrait;

    // ...

    public function rules()
    {
        return [
            ['image', 'string'], // Stores the filename
        ];
    }

    /**
     * @inheritdoc
     */
    protected function attributePaths()
    {
        return [
            'image' => 'images/'
        ];
    }

    // ...
}
```

Override the `attributePaths()` method to change the base path where the files will be saved on Flysystem (fs).

- You can map a different path to each file attribute of your `Model/ActiveRecord`.

#### Using Trait Methods

```php
$image = \yii\web\UploadedFile::getInstance($model, 'image');

// Save image_thumb.* to Flysystem (fs) on //my_bucket/images/ path
// The extension of the file will be determined by the submitted file type
// This allows multiple file types upload (png,jpg,gif,...)
// $model->image will hold "image_thumb.png" after this call finish with success
$model->saveUploadedFile($image, 'image', 'image_thumb');
$model->save();

// Save image_thumb.png to Flysystem (fs) on //my_bucket/images/ path
// The extension of the file will be determined by the submitted file type
// This force the extension to *.png
$model->saveUploadedFile($image, 'image', 'image_thumb.png', false);
$model->save();

// Remove the file with named saved on the image attribute
// Continuing the example, here "//my_bucket/images/my_image.png" will be deleted from Flysystem (fs)
$model->removeFile('image');
$model->save();

// Get the URL to the image on Flysystem (fs)
$model->getFileUrl('image');

// Get the presigned URL to the image on Flysystem (fs)
// The default duration is "+5 Minutes"
$model->getFilePresignedUrl('image');
```

#### Overriding Trait Methods

##### getFsComponent

The Flysystem (fs) MediaTrait depends on this component to be configured. The default configuration is to use this component on index `'fs'`, but you may use another value. For this cases, override the `getFsComponent()` method:

```php
public function getFsComponent()
{
    return Yii::$app->get('my_fs_component');
}
```

##### attributePaths

The main method to override is `attributePaths()`, which defines a path in Flysystem (fs) for each attribute of yout model. Allowing you to save each attribute in a different Flysystem (fs) folder.

Here an example:

```php
protected function attributePaths()
{
    return [
        'logo' => 'logos/',
        'badge' => 'images/badges/'
    ];
}

// or use another attribute, example: id
// ! Note: id must contain a value first if you don't want it to be empty

protected function attributePaths()
{
    return [
        'logo' => 'thumbnail/' . $this->id . '/logos/',
        'badge' => 'thumbnail/' . $this->id . '/images/badges/'
    ];
}
```

##### getPresignedUrlDuration

The default pressigned URL duration is set to "+5 Minutes", override this method and use your own expiration.
Return must instance of `DateTimeInterface`

```php
protected function getPresignedUrlDuration($attribute)
{
    return new \DateTimeImmutable('+2 Hours');
}

// or if you want to set the attribute differently

protected function getPresignedUrlDuration($attribute)
{
    switch ($attribute) {
        case 'badge':
            return new \DateTimeImmutable('+2 Hours');
            break;
        
        default:
            return new \DateTimeImmutable('+1 Days');
            break;
    }
}

```

The value should be a valid and instance of PHP DateTimeInterface. Read [PHP documentation](https://www.php.net/manual/en/class.datetimeinterface.php) for details.
