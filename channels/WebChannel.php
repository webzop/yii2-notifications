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

use Yii;
use webzop\notifications\Channel;
use webzop\notifications\Notification;


/**
 * Class WebChannel
 *
 * @package webzop\notifications\channels
 */
class WebChannel extends Channel
{

    /**
     * @var string
     */
    protected $title = array();

    /**
     * @var array
     */
    protected $options = array();

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

        $this->options = array(
            'body' => '',
            'data' => null,
            'icon' => 'images/ccard.png',
            'direction' => '',
            'image' => '',
            'badge' => '',
            "tag" => "request",
            'vibrate' => [200, 100, 200, 100, 200, 100, 400],
            "actions" => array(
                array(
                    "action" => "yes",
                    "title" => "Yes",
                    "icon" => "images/yes.png",
                ),
                array(
                    "action" => "no",
                    "title" => "No",
                    "icon" => "images/no.png",
                ),
            )
        );
    }


    /**
     * Send the web push notification
     *
     * @param Notification $notification
     */
    public function send(Notification $notification) {

        $this->title = $notification->getTitle();

        $this->options['body'] = $notification->getDescription();

        $notification->getData();
        $notification->getRoute();


    }

}
