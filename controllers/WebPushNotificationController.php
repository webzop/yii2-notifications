<?php

namespace webzop\notifications\controllers;

use http\Exception\InvalidArgumentException;
use webzop\notifications\model\WebPushSubscription;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class WebPushNotificationController extends Controller
{
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

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    /**
     * subscribe action
     */
    public function actionSubscribe()
    {
        $userId = null;
        if(Yii::$app->getUser()) {
            $userId = Yii::$app->getUser()->getId();
        }

        $request = Yii::$app->request;
        $subscription = $request->getRawBody();

        if(empty($subscription)) {
            throw new InvalidArgumentException('Missing subscription data');
        }

        $decoded = json_decode($subscription);
        $endpoint = $decoded->endpoint;

        // check if exists a subscriber with the same endpoint
        $subscriber = WebPushSubscription::findOne(['endpoint' => $endpoint]);

        $message = '';

        if($subscriber) {
            $subscriber->subscription = $subscription;
            $subscriber->save();
            $message = 'user subscription updated';
        }
        else {
            $subscriber = new WebPushSubscription();
            $subscriber->subscription = $subscription;
            $subscriber->endpoint = $endpoint;
            $subscriber->user_id = $userId;
            $subscriber->save();
            $message = 'user subscribed';
        }

        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = [
            'success' => true,
            'message' => $message
        ];

        return;
    }


    /**
     * unsubscribe action
     */
    public function actionUnsubscribe()
    {
        $request = Yii::$app->request;
        $subscription = $request->getRawBody();

        if(empty($subscription)) {
            throw new InvalidArgumentException('Missing subscription data');
        }

        $decoded = json_decode($subscription);
        $endpoint = $decoded->endpoint;

        $subscriber = WebPushSubscription::findOne(['endpoint' => $endpoint]);

        if($subscriber) {
            $subscriber->delete();
        }

        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = [
            'success' => true,
            'message' => 'user unsubscribed'
        ];
    }


    /**
     * @return \yii\console\Response|Response
     */
    public function actionServiceWorker() {
        $app_root = Yii::getAlias("@app");

        $filepath = '/service-worker.js';

        $module = Yii::$app->getModule('notifications');

        if(!empty($module->channels['web']['config']['serviceWorkerFilepath'])) {
            $filepath = $module->channels['web']['config']['serviceWorkerFilepath'];
        }

        return Yii::$app->response->sendFile($app_root . $filepath);
    }

}
