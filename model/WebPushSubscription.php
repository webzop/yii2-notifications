<?php

namespace webzop\notifications\model;

use ErrorException;
use Minishlink\WebPush\Subscription;
use yii\db\ActiveRecord;

class WebPushSubscription extends ActiveRecord implements WebNotificationRecipient
{

    /**
     * @inheritDoc
     * @throws ErrorException
     */
    public static function getUserSubscriptions($user_id = null) {

        $query = self::find();

        if($user_id) {
            $query->where(['user_id' => $user_id]);
        }

        $subscriptions = $query
            ->orderBy('id')
            ->all();


        $result = array();
        foreach($subscriptions as $subscription) {

            if($subscription->subscription) {
                $data = json_decode($subscription->subscription, true);
                $result[] = Subscription::create($data);
            }

        }

        return $result;
    }

}
