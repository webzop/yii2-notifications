<?php

/**
 * Notification object is composed by a title and an array options
 *
 * The three key steps to implementing push notifications are:
 *
 * - Adding the client side logic to subscribe a user to push (i.e. the JavaScript and UI in your web app that registers a user to push messages).
 * - The API call from your back-end / application that triggers a push message to a user's device.
 * - The service worker JavaScript file that will receive a "push event" when the push arrives on the device. It's in this JavaScript that you'll be able to show a notification.
 *
 */

namespace webzop\notifications\channels;

use ErrorException;
use http\Exception\InvalidArgumentException;
use Minishlink\WebPush\MessageSentReport;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webzop\notifications\model\WebNotificationRecipient;
use webzop\notifications\Channel;
use webzop\notifications\Notification;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;


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
    protected $enable = false;

    /**
     * enable/disable web channel notification
     * @var bool
     */
    protected $reuseVAPIDHeaders = true;

    /**
     * contains authentication data
     * @var array
     */
    protected $auth = array();



    /**
     * @var string
     */
    protected $title = array();

    /**
     * @var array
     */
    protected $options = [
        'TTL' => 300,               // defaults to 4 weeks (Time To Live in Seconds)
        'urgency' => 'normal',      // protocol defaults to "normal" (can be "very-low", "low", "normal", or "high")
        'topic' => 'new_event',     // not defined by default, this string will make the vendor show to the user only the last notification of this topic
        'batchSize' => 200,         // defaults to 1000
    ];



    /**
     * WebChannel constructor.
     *
     * @param $id
     * @param array $config
     */
    public function __construct($id, $config = []) {
        parent::__construct($id, $config);
        $this->defaultOptions();
    }

    /**
     * setup default options
     */
    public function defaultOptions() {

//        $this->options = array(
//            'body' => '',
//            'data' => null,
//            'icon' => 'images/ccard.png',
//            'direction' => '',
//            'image' => '',
//            'badge' => '',
//            "tag" => "request",
//            'vibrate' => [200, 100, 200, 100, 200, 100, 400],
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
//            )
//        );

    }


    /**
     * Send the web push notification
     *
     * @param Notification $notification
     * @param WebNotificationRecipient|null $recipient
     * @return bool true if at least one notification reach the recipient
     * @throws ErrorException
     */
    public function send(Notification $notification, WebNotificationRecipient $recipient = null) {

        if(!$this->enable) {
            return false;
        }

/*
        $this->title = $notification->getTitle();
        $this->options['body'] = $notification->getDescription();
        $notification->getData();
*/

        if(!$recipient) {
            return false;
            // throw new InvalidArgumentException('Missing web notification recipient.');
        }

        $subcriptions = $recipient->getSubscriptions();

        if(!$subcriptions) {
            return false;
            //throw new InvalidArgumentException('The specified recipient has no subscription.');
        }

        $webPush = new WebPush($this->auth);
        $webPush->setReuseVAPIDHeaders($this->reuseVAPIDHeaders);
        $webPush->setDefaultOptions($this->options);

        $payload = $notification->getTitle();

        // send all the notifications with payload
        foreach ($subcriptions as $subscription) {

            $webPush->sendNotification(
                $subscription,
                $payload
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
                echo "[v] Message sent successfully for subscription {$endpoint}.";
                $result = true;
            } else {
                echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";

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

    }

}
