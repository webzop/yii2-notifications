<?php

namespace webzop\notifications\channels;

use ErrorException;
use Minishlink\WebPush\MessageSentReport;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webzop\notifications\Channel;
use webzop\notifications\model\WebPushSubscription;
use webzop\notifications\Notification;
use Minishlink\WebPush\WebPush;
use yii\base\InvalidConfigException;


/**
 * Class WebChannel
 *
 * @package webzop\notifications\channels
 */
class WebChannel extends Channel
{

    /**
     * enable/disable web channel notification
     * @var bool
     */
    public $enable = false;

    /**
     * enable/disable web channel notification
     * @var bool
     */
    public $reuseVAPIDHeaders = true;

    /**
     * contains authentication data
     * @var array
     */
    public $auth = array();


    /**
     * default options for WebPush API
     * @var array
     */
    protected $options = [];


    /**
     * default data to push
     * @var array
     */
    protected $data = [];


    /**
     * WebChannel constructor.
     *
     * @param $id
     * @param array $config
     */
    public function __construct($id, $config = []) {
        parent::__construct($id, $config);
        $this->setDefaultOptions();
        $this->setDefaultData();
    }

    /**
     * setup default options
     */
    public function setDefaultOptions() {

        $this->options = [
            'TTL' => 300,               // defaults to 4 weeks (Time To Live in Seconds)
            'urgency' => 'normal',      // protocol defaults to "normal" (can be "very-low", "low", "normal", or "high")
            'batchSize' => 200,         // defaults to 1000
        ];

    }

    public function setDefaultData() {

        $this->data = [
            'timestamp' => time(),
            'requireInteraction' => true

//            'vibrate' => [200, 100, 200, 100, 400],

//            "actions" => array(
//                array(
//                    "action" => "yes",
//                    "title" => "Yes",
//                    "icon" => "images/yes.png",
//                ),
//                array(
//                    "action" => "no",
//                    "title" => "No",
//                    "icon" => "images/no.png",
//                ),
//            ),

//            'icon' => 'images/ccard.png',
//            'image' => '',
//            'badge' => '',

        ];

    }


    /**
     * Send the web push notification
     *
     * @param Notification $notification
     * @return bool true if at least one notification reach the recipient
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function send(Notification $notification) {

        if(!$this->enable) {
            return false;
        }

        $user_id = $notification->getUserId();
        $subscriptions = WebPushSubscription::getUserSubscriptions($user_id);

        if(!$subscriptions) {
            return false;
        }

        $webPush = new WebPush($this->auth);
        $webPush->setReuseVAPIDHeaders($this->reuseVAPIDHeaders);
        $webPush->setDefaultOptions($this->options);
        $webPush->setAutomaticPadding(false);       // fix for firefox (doesn't work with default)


        $payload = $notification->getTitle();

        $payload = array_merge($this->data, array(
            'title' => $notification->getTitle(),
            'body' => $notification->getDescription(),
            'data' => $notification->getData(),
        ));

        if($tag = $notification->getTag()) {
            $payload['tag'] = $tag;
        }


        // send all the notifications with payload
        foreach ($subscriptions as $subscription) {

            $webPush->sendNotification(
                $subscription,
                json_encode(
                    $payload
                )
            );
        }

        // result will be true if at least one notification reach the recipient
        $result = false;

        /**
         * Check sent results
         * @var MessageSentReport $report
         */
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                //echo "[v] Message sent successfully for subscription {$endpoint}.";
                $result = true;
            } else {
                //echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";

                // also available (to get more info)

                /** @var RequestInterface $requestToPushService */
                //$requestToPushService = $report->getRequest();

                /** @var ResponseInterface $responseOfPushService */
                //$responseOfPushService = $report->getResponse();

                /** @var string $failReason */
                //$failReason = $report->getReason();

                /** @var bool $isTheEndpointWrongOrExpired */
                //$isTheEndpointWrongOrExpired = $report->isSubscriptionExpired();

            }
        }

        return $result;

        return true;

    }

}
