<?php

namespace webzop\notifications;

use webzop\notifications\model\Notifications;
use Yii;
use yii\base\InvalidConfigException;

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
     * Time that has to pass until a notification with same user and key con be sent again.
     * It has to be a string that can be passed to @see \DateInterval (@link http://php.net/manual/en/dateinterval.construct.php)
     * If FALSE, this control is disabled
     * @var string
     */
    protected $renotification_time = FALSE;

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
     * @throws \Exception
     */
    public function shouldSend($channel)
    {
        // If the renotification_time params is false we don't need to check the interval
        if (empty($this->renotification_time)) {
            return TRUE;
        }

        // Workaround:
        // After the notification on the screen channel, the next are not sent because it finds the one just sent.
        // Adds 1 second to solve this problem.
        $margin = static::getLimit('PT1S')->getTimestamp();

        // The notification can be sent if there aren't others with same user/key sent in the period specified in
        // renotification_time params
        $end = static::getLimit($this->renotification_time)->getTimestamp();
        $notifications = Notifications::find()
            ->andWhere([
                'user_id' => $this->userId,
                'key'    => $this->key,
            ])
            ->andWhere(['>', 'created_at', $end])
            ->andWhere(['<', 'created_at', $margin])
            ->exists();

        return !$notifications;
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
        $module = Yii::$app->getModule('notifications');
        if(is_null($module)) {
            throw new InvalidConfigException("Please set up the module in the web/console settings, see README for instructions");
        }
        $module->send($this);
    }

    /**
     * Calculate a time limit subtractive the interval to the current moment
     * @param string $time interval, string passed to the constructor of \DateInterval
     * @return \DateTime
     * @throws \Exception
     */
    public static function getLimit($time)
    {
        return (new \DateTime())->sub(new \DateInterval($time));
    }

    /**
     * @return string
     */
    public function getRenotificationTime(): string
    {
        return $this->renotification_time;
    }

    /**
     * @param string $renotification_time
     */
    public function setRenotificationTime(string $renotification_time): void
    {
        $this->renotification_time = $renotification_time;
    }
}
