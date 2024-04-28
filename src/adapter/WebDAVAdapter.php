<?php

declare(strict_types=1);

namespace diecoding\flysystem\adapter;

use DateTimeInterface;
use diecoding\flysystem\traits\ChecksumAdapterTrait;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\Config;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;

final class WebDAVAdapter extends \League\Flysystem\WebDAV\WebDAVAdapter implements ChecksumProvider, TemporaryUrlGenerator
{
    use ChecksumAdapterTrait;

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, Config $config): string
    {
        return $this->publicUrl($path, $config);
    }
}
