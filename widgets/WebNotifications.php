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
     * @var string filepath of service-worker.js
     */
    public $serviceWorkerFilepath = '/service-worker.js';

    /**
     * @var string VAPID public key
     */
    public $vapid_pub_key = null;

    /**
     * @var string button label for subscribe button
     */
    public $subscribeButtonLabel = 'Subscribe';

    /**
     * @var string button label for unsubscribe button
     */
    public $unsubscribeButtonLabel = 'Unsubscribe';

    /**
     * @var string subscribe url
     */
    public $subscribeUrl = null;

    /**
     * @var string unsubscribe url
     */
    public $unsubscribeUrl = null;

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

        // set subscribe and unsubscribe urls
        if(!$this->subscribeUrl) {
            $this->subscribeUrl = Url::to(['/notifications/web-push-notification/subscribe']);
        }
        if(!$this->unsubscribeUrl) {
            $this->unsubscribeUrl = Url::to(['/notifications/web-push-notification/unsubscribe']);
        }

        // set VAPID publis key
        $this->vapid_pub_key = Yii::$app->params['VAPID_public_key'];
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
        $js = 'WebNotifications(' . Json::encode($this->getParams()) . ');';
        $view = $this->getView();

        WebNotificationsAsset::register($view);

        $view->registerJs($js);
    }

    /**
     * @return array
     */
    protected function getParams() {
        return array(
            'service_worker_filepath' => $this->serviceWorkerFilepath,
            'vapid_pub_key' => $this->vapid_pub_key,
            'subscribe_button_label' => $this->subscribeButtonLabel,
            'unsubscribe_button_label' => $this->unsubscribeButtonLabel,
            'subscribe_url' => $this->subscribeUrl,
            'unsubscribe_url' => $this->unsubscribeUrl,
        );
    }


}
