<?php

namespace webzop\notifications;

use Yii;

/**
 * This is the base class for a notification.
 *
 * @property string $key
 * @property integer $userId
 * @property array $data
 */
abstract class Notification extends \yii\base\BaseObject
{
    public $key;

    public $userId = 0;

    public $data = [];

    /**
     * Create an instance
     *
     * @param string $key
     * @param array $params notification properties
     * @return static the newly created Notification
     * @throws \Exception
     */
    public static function create($key, $params = []){
        $params['key'] = $key;
        return new static($params);
    }

    /**
     * Determines if the notification can be sent.
     *
     * @param  \webzop\notifications\Channel $channel
     * @return bool
     */
    public function shouldSend($channel)
    {
        return true;
    }

    /**
     * Gets the notification title
     *
     * @return string
     */
    abstract public function getTitle();

    /**
     * Gets the notification description
     *
     * @return string|null
     */
    public function getDescription(){
        return null;
    }

    /**
     * Gets the notification route
     *
     * @return array|null
     */
    public function getRoute(){
        return null;
    }

    /**
     * Gets notification data
     *
     * @return array
     */
    public function getData(){
        return $this->data;
    }

    /**
     * Sets notification data
     *
     * @return self
     */
    public function setData($data = []){
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the UserId
     *
     * @return array
     */
    public function getUserId(){
        return $this->userId;
    }

    /**
     * Sets the UserId
     *
     * @return self
     */
    public function setUserId($id){
        $this->userId = $id;
        return $this;
    }

    /**
     * Sends this notification to all channels
     *
     * @param string $key The key of the notification
     * @param integer $userId The user id that will get the notification
     * @param array $data Additional data information
     * @throws \Exception
     */
    public function send(){
        Yii::$app->getModule('notifications')->send($this);
    }

}
