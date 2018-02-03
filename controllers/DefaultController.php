<?php

namespace webzop\notifications\controllers;

use Yii;
use yii\web\Controller;
use yii\db\Query;
use yii\data\Pagination;
use yii\helpers\Url;
use webzop\notifications\helpers\TimeElapsed;
use webzop\notifications\widgets\Notifications;

class DefaultController extends Controller
{

    public $layout = "@app/views/layouts/main";

    /**
     * Displays index page.
     *
     * @return string
     */
    public function actionIndex()
    {
        $userId = Yii::$app->getUser()->getId();
        $query = (new Query())
            ->from('{{%notifications}}')
            ->andWhere(['or', 'user_id = 0', 'user_id = :user_id'], [':user_id' => $userId]);

        $pagination = new Pagination([
            'pageSize' => 20,
            'totalCount' => $query->count(),
        ]);

        $list = $query
            ->orderBy(['id' => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $notifs = $this->prepareNotifications($list);

        return $this->render('index', [
            'notifications' => $notifs,
            'pagination' => $pagination,
        ]);
    }

    public function actionList()
    {
        $userId = Yii::$app->getUser()->getId();
        $list = (new Query())
            ->from('{{%notifications}}')
            ->andWhere(['or', 'user_id = 0', 'user_id = :user_id'], [':user_id' => $userId])
            ->orderBy(['id' => SORT_DESC])
            ->limit(10)
            ->all();
        $notifs = $this->prepareNotifications($list);
        $this->ajaxResponse(['list' => $notifs]);
    }

    public function actionCount()
    {
        $count = Notifications::getCountUnseen();
        $this->ajaxResponse(['count' => $count]);
    }

    public function actionRead($id)
    {
        Yii::$app->getDb()->createCommand()->update('{{%notifications}}', ['read' => true], ['id' => $id])->execute();

        if(Yii::$app->getRequest()->getIsAjax()){
            return $this->ajaxResponse(1);
        }

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    public function actionReadAll()
    {
        Yii::$app->getDb()->createCommand()->update('{{%notifications}}', ['read' => true])->execute();
        if(Yii::$app->getRequest()->getIsAjax()){
            return $this->ajaxResponse(1);
        }

        Yii::$app->getSession()->setFlash('success', Yii::t('modules/notifications', 'All notifications have been marked as read.'));

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    public function actionDeleteAll()
    {
        Yii::$app->getDb()->createCommand()->delete('{{%notifications}}')->execute();

        if(Yii::$app->getRequest()->getIsAjax()){
            return $this->ajaxResponse(1);
        }

        Yii::$app->getSession()->setFlash('success', Yii::t('modules/notifications', 'All notifications have been deleted.'));

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    private function prepareNotifications($list){
        $notifs = [];
        $seen = [];
        foreach($list as $notif){
            if(!$notif['seen']){
                $seen[] = $notif['id'];
            }
            $route = @unserialize($notif['route']);
            $notif['url'] = !empty($route) ? Url::to($route) : '';
            $notif['timeago'] = TimeElapsed::timeElapsed($notif['created_at']);
            $notifs[] = $notif;
        }

        if(!empty($seen)){
            Yii::$app->getDb()->createCommand()->update('{{%notifications}}', ['seen' => true], ['id' => $seen])->execute();
        }

        return $notifs;
    }

    public function ajaxResponse($data = [])
    {
        if(is_string($data)){
            $data = ['html' => $data];
        }

        $session = \Yii::$app->getSession();
        $flashes = $session->getAllFlashes(true);
        foreach ($flashes as $type => $message) {
            $data['notifications'][] = [
                'type' => $type,
                'message' => $message,
            ];
        }
        return $this->asJson($data);
    }

}
