<?php

namespace diecoding\flysystem;

use Yii;
use yii\base\Action;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * ErrorAction displays application errors using a specified view.
 *
 * To use ErrorAction, you need to do the following steps:
 *
 * First, declare an action of ErrorAction type in the `actions()` method of your `SiteController`
 * class (or whatever controller you prefer), like the following:
 *
 * ```php
 * public function actions()
 * {
 *     return [
 *         'error' => ['class' => 'yii\web\ErrorAction'],
 *     ];
 * }
 * ```
 *
 * Then, create a view file for this action. If the route of your error action is `site/error`, then
 * the view file should be `views/site/error.php`. In this view file, the following variables are available:
 *
 * - `$name`: the error name
 * - `$message`: the error message
 * - `$exception`: the exception being handled
 *
 * Finally, configure the "errorHandler" application component as follows,
 *
 * ```php
 * 'errorHandler' => [
 *     'errorAction' => 'site/error',
 * ]
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0
 */
class LocalAction extends Action
{
    /**
     * @var string flysystem component
     */
    public $component = 'fs';

    /**
     * @var LocalComponent
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
     * @return string result content
     * @throws NotFoundHttpException if data not valid
     */
    public function run($data)
    {
        try {
            $params = Json::decode($this->filesystem->decrypt($data));

            $now      = time();
            $filePath = (string) $params['filePath'];
            $expires  = $params['expires'];

            if ($expires !== null && (int) $expires < $now) {
                $filePath = false;
            }
        } catch (\Throwable $th) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $normalizePath = '/' . $this->filesystem->normalizePath($filePath);
        if ($filePath === false || !$this->filesystem->fileExists($normalizePath)) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $filePath = $this->filesystem->path . $normalizePath;

        return Yii::$app->getResponse()->sendFile($filePath, $normalizePath, [
            'inline' => true,
        ]);
    }
}
