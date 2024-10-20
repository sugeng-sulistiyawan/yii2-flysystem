<?php

declare(strict_types=1);

namespace diecoding\flysystem\adapter;

use diecoding\flysystem\traits\ChecksumAdapterTrait;
use diecoding\flysystem\traits\UrlGeneratorAdapterTrait;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;

/**
 * This properties for fixed issue https://scrutinizer-ci.com/g/sugeng-sulistiyawan/yii2-flysystem/inspections/22f77797-37d0-419a-9447-ab6111572e99/issues/?status=all
 * @property string $action
 * @property string $prefix
 */
final class GoogleDriveAdapter extends \Masbug\Flysystem\GoogleDriveAdapter implements ChecksumProvider, PublicUrlGenerator, TemporaryUrlGenerator
{
    use UrlGeneratorAdapterTrait, ChecksumAdapterTrait;
}
