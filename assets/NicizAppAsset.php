<?php


namespace app\modules\niciz\assets;
use yii\web\AssetBundle;

class NicizAppAsset extends AssetBundle {

    // the alias to your assets folder in your file system
    public $sourcePath = '@niciz-assets';
    public $css = [
        'css/notification.css',
    ];

    public $js = [
        'js/notification.js',
        'js/web-notification.js',
        'js/service-worker.js',
    ];

    // that are the dependecies, for makeing your Asset bundle work with Yii2 framework
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

}