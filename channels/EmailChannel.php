<?php

namespace webzop\notifications\channels;

use Yii;
use yii\di\Instance;
use yii\base\InvalidConfigException;
use webzop\notifications\Channel;
use webzop\notifications\Notification;

class EmailChannel extends Channel
{
    /**
     * @var array the configuration array for creating a [[\yii\mail\MessageInterface|message]] object.
     * Note that the "to" option must be set, which specifies the destination email address(es).
     */
    public $message = [];

    /**
     * @var \yii\mail\MailerInterface|array|string the mailer object or the application component ID of the mailer object.
     * After the EmailChannel object is created, if you want to change this property, you should only assign it
     * with a mailer object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $mailer = 'mailer';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->mailer = Instance::ensure($this->mailer, 'yii\mail\MailerInterface');
    }

    /**
     * Sends a notification in this channel.
     */
    public function send(Notification $notification)
    {
        $message = $this->composeMessage($notification);
        $message->send($this->mailer);
    }

    /**
     * Composes a mail message with the given body content.
     * @param \webzop\notifications\Notification $notification the body content
     * @return \yii\mail\MessageInterface $message
     * @throws InvalidConfigException
     */
    protected function composeMessage($notification)
    {
        if(empty($this->message['to'])){
            throw new InvalidConfigException('The "to" option must be set in EmailChannel::message.');
        }
        $message = $this->mailer->compose();
        Yii::configure($message, $this->message);
        $message->setSubject((string)$notification->getTitle());
        $message->setTextBody((string)$notification->getDescription());
        return $message;
    }
}
