<?php

namespace webzop\notifications\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\db\Query;
use webzop\notifications\NotificationsAsset;


class Notifications extends \yii\base\Widget
{

    public $options = ['class' => 'dropdown nav-notifications'];

    /**
     * @var array additional options to be passed to the notification library.
     * Please refer to the plugin project page for available options.
     */
    public $clientOptions = [];
    /**
     * @var integer the XHR timeout in milliseconds
     */
    public $xhrTimeout = 2000;
    /**
     * @var integer The delay between pulls in milliseconds
     */
    public $pollInterval = 60000;

    /**
     * @inheritdoc
     */
    public function run()
    {

        if(!isset($this->options['id'])){
            $this->options['id'] = $this->getId();
        }

        $html  = Html::beginTag('li', $this->options);
        $html .= Html::beginTag('a', ['href' => '#', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown']);
        $html .= Html::tag('span', '', ['class' => 'glyphicon glyphicon-bell']);
        $count = self::getCountUnseen();
        $countOptions = ['class' => 'badge badge-warning navbar-badge notifications-count', 'data-count' => $count];
        if(!$count){
            $countOptions['style'] = 'display: none;';
        }
        $html .= Html::tag('span', $count, $countOptions);
        $html .= Html::endTag('a');
        $html .= Html::begintag('div', ['class' => 'dropdown-menu']);
        $header = Html::a(Yii::t('app', 'Mark all as read'), '#', ['class' => 'read-all pull-right']);
        $header .= Yii::t('app', 'Notifications');
        $html .= Html::tag('div', $header, ['class' => 'header']);

        $html .= Html::begintag('div', ['class' => 'notifications-list']);
        //$html .= Html::tag('div', '<span class="ajax-loader"></span>', ['class' => 'loading-row']);
        $html .= Html::tag('div', Html::tag('span', Yii::t('app', 'There are no notifications to show'), ['style' => 'display: none;']), ['class' => 'empty-row']);
        $html .= Html::endTag('div');

        $footer = Html::a(Yii::t('app', 'View all'), ['/notifications/default/index']);
        $html .= Html::tag('div', $footer, ['class' => 'footer']);
        $html .= Html::endTag('div');
        $html .= Html::endTag('li');

        echo $html;

        $this->registerAssets();
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $this->clientOptions = array_merge([
            'id' => $this->options['id'],
            'url' => Url::to(['/notifications/default/list']),
            'countUrl' => Url::to(['/notifications/default/count']),
            'readUrl' => Url::to(['/notifications/default/read']),
            'readAllUrl' => Url::to(['/notifications/default/read-all']),
            'xhrTimeout' => Html::encode($this->xhrTimeout),
            'pollInterval' => Html::encode($this->pollInterval),
        ], $this->clientOptions);

        $js = 'Notifications(' . Json::encode($this->clientOptions) . ');';
        $view = $this->getView();

        NotificationsAsset::register($view);

        $view->registerJs($js);
    }

    public static function getCountUnseen(){
        $userId = Yii::$app->getUser()->getId();
        $count = (new Query())
            ->from('notifications')
            ->andWhere(['or', 'user_id = 0', 'user_id = :user_id'], [':user_id' => $userId])
            ->andWhere(['seen' => 0])
            ->count();
        return $count;
    }

}
