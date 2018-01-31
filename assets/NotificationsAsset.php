<?php

namespace webzop\notifications\assets;

use yii\web\AssetBundle;

/**
 * Class NotificationsAsset
 *
 * @package webzop\notifications
 */
class NotificationsAsset extends AssetBundle
{
    
    /**
     * @inheritdoc
     */
    public $js = [
        'js/notifications.js',
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'css/notifications.css',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
    ];

}
