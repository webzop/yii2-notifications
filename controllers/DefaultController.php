<?php

namespace webzop\notifications\controllers;

use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\db\Query;
use yii\data\Pagination;
use yii\helpers\Url;
use webzop\notifications\helpers\TimeElapsed;
use webzop\notifications\widgets\Notifications;
use yii\web\Response;

class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ]
            ],
        ];
    }

    public $layout = "@app/views/layouts/main";

    /**
     * Displays index page.
     * @return string
     */
    public function actionIndex()
    {
        $userId = Yii::$app->getUser()->getId();
        $query = (new Query())
            ->from('{{%notifications}}')
            ->andWhere(['or', 'user_id = 0', 'user_id = :user_id'], [':user_id' => $userId]);

        $pagination = new Pagination(
            [
                'pageSize'   => 20,
                'totalCount' => $query->count(),
            ]
        );

        $list = $query
            ->orderBy(['id' => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $notifs = $this->prepareNotifications($list);

        return $this->render(
            'index',
            [
                'notifications' => $notifs,
                'pagination'    => $pagination,
            ]
        );
    }

    /**
     * Get a list of all notifications.
     * @return Response
     */
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
        return $this->ajaxResponse(['list' => $notifs]);
    }

    /**
     * Get the amount of unseen notifications.
     * @return Response
     */
    public function actionCount()
    {
        $count = Notifications::getCountUnseen();
        return $this->ajaxResponse(['count' => $count]);
    }

    /**
     * Mark a notification as read.
     * @param $id
     * @return Response
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\db\Exception
     */
    public function actionRead($id)
    {
        Yii::$app->getDb()->createCommand()->update('{{%notifications}}', ['read' => true], ['id' => $id])->execute();

        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->ajaxResponse(1);
        }

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    /**
     * Mark a notification as unread.
     * @param $id
     * @return Response
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\db\Exception
     */
    public function actionUnread($id)
    {
        Yii::$app->getDb()->createCommand()->update('{{%notifications}}', ['read' => false], ['id' => $id])->execute();

        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->ajaxResponse(1);
        }

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    /**
     * Mark all notifications as read.
     * @return Response
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\db\Exception
     */
    public function actionReadAll()
    {
        Yii::$app->getDb()->createCommand()->update(
            '{{%notifications}}',
            ['read' => true, 'seen' => true],
            ['user_id' => Yii::$app->user->id]
        )->execute();
        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->ajaxResponse(1);
        }

        Yii::$app->getSession()->setFlash('success', Yii::t('modules/notifications', 'All notifications have been marked as read.'));

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    /**
     * Delete all notifications.
     * @return Response
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\db\Exception
     */
    public function actionDeleteAll()
    {
        Yii::$app->getDb()->createCommand()->delete('{{%notifications}}')->execute();

        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->ajaxResponse(1);
        }

        Yii::$app->getSession()->setFlash('success', Yii::t('modules/notifications', 'All notifications have been deleted.'));

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    /**
     * Create an array of the given notifications as returned by /list
     * and mark them all as seen.
     * @param array $list
     * @return array
     * @throws Exception
     */
    private function prepareNotifications($list)
    {
        $notifs = [];
        $seen = [];
        foreach ($list as $notif) {
            if (!$notif['seen']) {
                $seen[] = $notif['id'];
            }
            $route = @unserialize($notif['route']);
            $notif['url'] = !empty($route) ? Url::to($route) : '';
            $notif['timeago'] = TimeElapsed::timeElapsed($notif['created_at']);
            $notifs[] = $notif;
        }

        if (!empty($seen)) {
            Yii::$app->getDb()->createCommand()->update('{{%notifications}}', ['seen' => true], ['id' => $seen])->execute();
        }

        return $notifs;
    }

    public function ajaxResponse($data = [])
    {
        if (is_string($data)) {
            $data = ['html' => $data];
        }

        $session = \Yii::$app->getSession();
        $flashes = $session->getAllFlashes(true);
        foreach ($flashes as $type => $message) {
            $data['notifications'][] = [
                'type'    => $type,
                'message' => $message,
            ];
        }
        return $this->asJson($data);
    }

}
