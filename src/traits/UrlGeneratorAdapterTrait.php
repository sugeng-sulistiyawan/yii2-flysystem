<?php

namespace diecoding\flysystem\traits;

use DateTimeInterface;
use diecoding\flysystem\AbstractComponent;
use League\Flysystem\Config;
use League\Flysystem\PathPrefixer;
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
     * @var AbstractComponent
     */
    public $component;

    /**
     * @var bool
     */
    protected $skipPrefixer = false;

    public function publicUrl(string $path, /** @scrutinizer ignore-unused */Config $config): string
    {
        // TODO: Use absolute path and don't encrypt
        if ($this->skipPrefixer !== false && $this->component->prefix) {
            $prefixer = new PathPrefixer((string) $this->component->prefix);
            $path = $prefixer->stripPrefix($path);
        }
        $params = [
            'path' => $path,
            'expires' => 0,
        ];

        return Url::toRoute([$this->component->action, 'data' => $this->component->/** @scrutinizer ignore-call */encrypt(Json::encode($params))], true);
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, /** @scrutinizer ignore-unused */Config $config): string
    {
        // TODO: Use absolute path and don't encrypt
        if ($this->skipPrefixer !== false && $this->component->prefix) {
            $prefixer = new PathPrefixer((string) $this->component->prefix);
            $path = $prefixer->stripPrefix($path);
        }
        $params = [
            'path' => $path,
            'expires' => (int) $expiresAt->getTimestamp(),
        ];

        return Url::toRoute([$this->component->action, 'data' => $this->component->/** @scrutinizer ignore-call */encrypt(Json::encode($params))], true);
    }
}
