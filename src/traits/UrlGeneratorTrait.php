<?php

namespace diecoding\flysystem\traits;

use DateTimeInterface;
use League\Flysystem\Config;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Trait UrlGeneratorTrait for Model
 * 
 * @method string normalizePath(string $path, \League\Flysystem\PathNormalizer $pathNormalizer = null)
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
trait UrlGeneratorTrait
{
    use EncrypterTrait;

    /**
     * @var string
     */
    public $action = '/site/file';

    public function publicUrl(string $path, Config $config): string
    {
        $params = [
            'path'    => $this->normalizePath($path),
            'expires' => 0,
            'config'  => $config,
        ];

        return Url::toRoute([$this->action, 'data' => $this->encrypt(Json::encode($params))], true);
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, Config $config): string
    {
        $params = [
            'path'    => $this->normalizePath($path),
            'expires' => (int) $expiresAt->getTimestamp(),
            'config'  => $config,
        ];

        return Url::toRoute([$this->action, 'data' => $this->encrypt(Json::encode($params))], true);
    }
}
