<?php

namespace diecoding\flysystem\traits;

/**
 * Trait UrlGeneratorComponentTrait for Component
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
trait UrlGeneratorComponentTrait
{
    use EncrypterTrait;

    /**
     * @var string
     */
    public $action = '/site/file';
}
