<?php

namespace diecoding\flysystem\traits;

use League\Flysystem\Config;
use League\Flysystem\UnableToProvideChecksum;

/**
 * Trait ChecksumAdapterTrait for Adapter
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
trait ChecksumAdapterTrait
{
    public function checksum(string $path, Config $config): string
    {
        $algo = $config->get('checksum_algo', 'md5');
        $contents = $this->read($path);
        error_clear_last();
        $checksum = @hash($algo, $contents);

        if ($checksum === false) {
            throw new UnableToProvideChecksum(error_get_last()['message'] ?? '', $path);
        }

        return $checksum;
    }
}