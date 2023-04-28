<?php

namespace diecoding\flysystem;

use Yii;
use yii\base\Action;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * LocalAction for handle LocalComponent.
 *
 * To use LocalAction, you need to do the following steps:
 *
 * First, declare an action of LocalAction type in the `actions()` method of your `SiteController`
 * class (or whatever controller you prefer), like the following:
 *
 * ```php
 * public function actions()
 * {
 *     return [
 *         'file' => [
 *             'class' => \diecoding\flysystem\LocalAction::class,
 *             'component' => 'fs',
 *         ],
 *     ];
 * }
 * ```
 * 
 * @package diecoding\flysystem
 * 
 * @link      https://sugengsulistiyawan.my.id/
 * @author    Sugeng Sulistiyawan <sugeng.sulistiyawan@gmail.com>
 * @copyright Copyright (c) 2023
 */
class LocalAction extends Action
{
    /**
     * @var string flysystem component
     */
    public $component = 'fs';

    /**
     * @var mixed|LocalComponent
     */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->filesystem = Yii::$app->get($this->component);
    }

    /**
     * Runs the action.
     *
     * @param string $data
     * @return mixed result content
     * @throws NotFoundHttpException if data not valid
     */
    public function run($data)
    {
        try {
            $params = Json::decode($this->filesystem->decrypt($data));

            $now      = time();
            $filePath = $params['filePath'];
            $expires  = $params['expires'];

            if ($expires !== null && (int) $expires < $now) {
                $filePath = '';
            }
        } catch (\Throwable $th) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $normalizePath = '/' . $this->filesystem->normalizePath($filePath);
        if ($filePath === '' || !$this->filesystem->fileExists($normalizePath)) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $filePath = $this->filesystem->path . $normalizePath;

        return Yii::$app->getResponse()->sendFile($filePath, $normalizePath, [
            'inline' => true,
        ]);
    }
}
