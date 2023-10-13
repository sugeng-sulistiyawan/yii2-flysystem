<?php

namespace diecoding\flysystem\traits;

use DateTimeInterface;
use diecoding\flysystem\AbstractComponent;
use League\Flysystem\Config;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Trait UrlGeneratorAdapterTrait for Adapter
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
trait UrlGeneratorAdapterTrait
{
    /**
     * @var AbstractComponent|UrlGeneratorComponentTrait
     */
    public $component;

    public function publicUrl(string $path, Config $config): string
    {
        // TODO: Use absolute path and don't encrypt
        $params = [
            'path' => $path,
            'expires' => 0,
        ];

        return Url::toRoute([$this->component->action, 'data' => $this->component->encrypt(Json::encode($params))], true);
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, Config $config): string
    {
        // TODO: Use absolute path and don't encrypt
        $params = [
            'path' => $path,
            'expires' => (int) $expiresAt->getTimestamp(),
        ];

        return Url::toRoute([$this->component->action, 'data' => $this->component->encrypt(Json::encode($params))], true);
    }
}