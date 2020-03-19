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
     * @var string|null
     */
    public $tag = null;

    /**
     * @var string|null
     */
    public $priority = null;

    /**
     * @var string|null
     */
    public $ttl = null;


    const PRIORITY_LOWEST = 'very-low';
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';


    const DEFAULT_TTL = 7200;               // [in seconds]. 7200 = valid for two hours

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
     * @param Channel $channel
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
     * @param array $data
     * @return self
     */
    public function setData($data = []){
        $this->data = $data;
        return $this;
    }

    /**
     * Gets notification tag
     *
     * @return string|null
     */
    public function getTag(){
        return $this->tag;
    }

    /**
     * Sets notification tag
     *
     * @param string|null $tag
     * @return self
     */
    public function setTag($tag = null){
        $this->tag = $tag;
        return $this;
    }

    /**
     * Sets notification priority
     *
     * @param string $priority
     * @return self
     */
    public function setPriority($priority){
        $this->priority = $priority;
        return $this;
    }

    /**
     * Gets notification priority
     *
     * @return string
     */
    public function getPriority(){
        if($this->priority) {
            return $this->priority;
        }
        return Notification::PRIORITY_NORMAL;
    }

    /**
     * Sets notification TTL
     *
     * @param string $ttl
     * @return self
     */
    public function setTTL($ttl){
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * Gets notification TTL
     *
     * @return string
     */
    public function getTTL(){
        if($this->ttl) {
            return $this->ttl;
        }
        return Notification::DEFAULT_TTL;
    }


    /**
     * Gets the UserId
     *
     * @return int
     */
    public function getUserId(){
        return $this->userId;
    }

    /**
     * Sets the UserId
     *
     * @param int $id
     * @return self
     */
    public function setUserId($id){
        $this->userId = $id;
        return $this;
    }

    /**
     * Sends this notification to all channels
     *
     */
    public function send(){
        Yii::$app->getModule('notifications')->send($this);
    }

}
