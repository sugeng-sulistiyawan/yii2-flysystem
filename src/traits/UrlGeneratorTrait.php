<?php

namespace diecoding\flysystem\traits;

use DateTimeInterface;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Trait UrlGeneratorTrait for Model
 * 
 * @package diecoding\flysystem\traits
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

    public function publicUrl(string $path, array $config = []): string
    {
        $params = [
            'path'    => $this->normalizePath($path),
            'expires' => 0,
            'config'  => $config,
        ];

        return Url::toRoute([$this->action, 'data' => $this->encrypt(Json::encode($params))], true);
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, array $config = []): string
    {
        $params = [
            'path'    => $this->normalizePath($path),
            'expires' => (int) $expiresAt->getTimestamp(),
            'config'  => $config,
        ];

        return Url::toRoute([$this->action, 'data' => $this->encrypt(Json::encode($params))], true);
    }
}
