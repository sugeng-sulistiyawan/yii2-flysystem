<?php

declare(strict_types=1);

namespace diecoding\flysystem\adapter;

use diecoding\flysystem\traits\ChecksumAdapterTrait;
use diecoding\flysystem\traits\UrlGeneratorAdapterTrait;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;

final class FtpAdapter extends \League\Flysystem\Ftp\FtpAdapter implements ChecksumProvider, PublicUrlGenerator, TemporaryUrlGenerator
{
    use UrlGeneratorAdapterTrait, ChecksumAdapterTrait;
}