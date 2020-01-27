<?php

namespace webzop\notifications;

use yii\web\AssetBundle;

/**
 * Class NotificationsAsset
 *
 * @package webzop\notifications
 */
class WebNotificationsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__.'/assets';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/web-notifications.js',
    ];

}
