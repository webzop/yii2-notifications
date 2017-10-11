<?php

namespace webzop\notifications;

use Yii;
use yii\base\InvalidParamException;

class Module extends \yii\base\Module
{

    public $channels = [];

    /**
     * Send a notification to all channels
     *
     * @param Notification $notification
     * @param array|null $channels
     * @return Channel|null return the channel
     */
    public function send($notification, array $channels = null){
        if($channels === null){
            $channels = array_keys($this->channels);
        }

        foreach ((array)$channels as $id) {
            $channel = $this->getChannel($id);
            if(!$notification->shouldSend($channel)){
                continue;
            }

            $handle = 'to'.ucfirst($id);
            try {
                if($notification->hasMethod($handle)){
                    call_user_func([clone $notification, $handle], $channel);
                }
                else {
                    $channel->send(clone $notification);
                }
            } catch (\Exception $e) {
                Yii::warning("Notification sended by channel '$id' has failed: " . $e->getMessage(), __METHOD__);
            }
        }
    }

    /**
     * Gets the channel instance
     *
     * @param string $id the id of the channel
     * @return Channel|null return the channel
     * @throws InvalidParamException
     */
    public function getChannel($id){
        if(!isset($this->channels[$id])){
            throw new InvalidParamException("Unknown channel '{$id}'.");
        }

        if (!is_object($this->channels[$id])) {
            $this->channels[$id] = $this->createChannel($id, $this->channels[$id]);
        }

        return $this->channels[$id];
    }

    protected function createChannel($id, $config){
        return Yii::createObject($config, [$id]);
    }

}
