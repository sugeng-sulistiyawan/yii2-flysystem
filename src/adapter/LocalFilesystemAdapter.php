<?php

declare(strict_types=1);

namespace diecoding\flysystem\adapter;

use diecoding\flysystem\traits\UrlGeneratorAdapterTrait;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;

final class LocalFilesystemAdapter extends \League\Flysystem\Local\LocalFilesystemAdapter implements PublicUrlGenerator, TemporaryUrlGenerator
{
    use UrlGeneratorAdapterTrait;
}
