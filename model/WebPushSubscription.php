<?php

namespace app\models;

use ErrorException;
use Minishlink\WebPush\Subscription;
use webzop\notifications\model\WebNotificationRecipient;
use yii\db\ActiveRecord;

class WebPushSubscription extends ActiveRecord implements WebNotificationRecipient
{

    /**
     * @inheritDoc
     * @throws ErrorException
     */
    public static function getUserSubscriptions($user_id = null) {

        $subscriptions = self::find();

        if($user_id) {
            $subscriptions->where(['user_id' => $user_id]);
        }

        $subscriptions
            ->orderBy('id')
            ->all();


        $result = array();
        foreach($subscriptions as $subscription) {
            $result[] = Subscription::create($subscription);
        }

        return $result;
    }


}
