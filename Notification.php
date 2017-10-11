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
     * @param array $data additional data information
     * @return static the newly created Notification
     * @throws \Exception
     */
    public static function create($key, $data = []){
        return new static([
            'key' => $key,
            'data' => $data,
        ]);
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
     * Gets the notification data
     *
     * @return array
     */
    public function getData(){
        return $this->data;
    }

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

    public function setUserId($id){
        $this->userId = $id;
        return $this;
    }

    /**
     * Alias to Sends a notification to all channels
     *
     * @param string $key The key of the notification
     * @param integer $userId The user id that will get the notification
     * @param array $data Additional data information
     * @throws \Exception
     */
    public static function notify($key, $userId, $data = [])
    {
        self::create($key, $data)->setUserId($userId)->send();
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
