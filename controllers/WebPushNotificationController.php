<?php

namespace webzop\notifications\controllers;

use webzop\notifications\model\WebPushSubscription;
use yii\web\Controller;

class WebPushNotificationController extends Controller
{

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

        // TODO: fix user id
        // $userId = Yii::$app->getUser()->getId();

        $request = \Yii::$app->request;
        $subscription = $request->getRawBody();

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
            $subscriber->user_id = 2;
            $subscriber->save();
            $message = 'user subscribed';
        }

        $response = \Yii::$app->response;
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
        // TODO: fai qualche controllo in più sulle variabili

        $request = \Yii::$app->request;
        $subscription = $request->getRawBody();

        $decoded = json_decode($subscription);
        $endpoint = $decoded->endpoint;

        $subscriber = WebPushSubscription::findOne(['endpoint' => $endpoint]);

        if($subscriber) {
            $subscriber->delete();
        }

        $response = \Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = [
            'success' => true,
            'message' => 'user unsubscribed'
        ];
    }

}
