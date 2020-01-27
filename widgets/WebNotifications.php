<?php

namespace webzop\notifications\widgets;

use webzop\notifications\WebNotificationsAsset;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;


class WebNotifications extends \yii\base\Widget
{

    /**
     * settable options for module
     * @var array
     */
    public $options = [];

    /**
     *
     */
    public function init()
    {
        parent::init();

        if(!isset($this->options['id'])){
            $this->options['id'] = $this->getId();
        }

        if(!isset($this->options['service_worker_filepath'])){
            $this->options['service_worker_filepath'] = '/service-worker.js';
        }

        // set subscribe and unsubscribe urls
        $this->options['subscribe_url'] = Url::to(['/notifications/push-notification/subscribe']);
        $this->options['unsubscribe_url'] = Url::to(['/notifications/push-notification/unsubscribe']);

        // set VAPID publis key
        $this->options['vapid_pub_key'] = Yii::$app->params['VAPID_public_key'];

    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo $this->renderSubscribeButton();
        $this->registerAssets();
    }

    /**
     * @inheritdoc
     */
    protected function renderSubscribeButton()
    {
        $html = Html::beginTag('p');
            $html .= Html::beginTag('button', ['class' => 'js-web-push-subscribe-button', 'disabled' => 'disabled']);
                $html .= "Subscribe";
            $html .= Html::endTag('button');
        $html .= Html::endTag('p');

        return $html;
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $js = 'WebNotifications(' . Json::encode($this->options) . ');';
        $view = $this->getView();

        WebNotificationsAsset::register($view);

        $view->registerJs($js);
    }

}
