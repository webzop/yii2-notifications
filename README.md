# yii2-notifications
This module provides a way to sending notifications across a variety of delivery channels, including mail, screen, SMS (via Nexmo), etc. Notifications may also be stored in a database so they may be displayed in your web interface.

Notifications are short messages that notify users of something that occurred in your application. For example, if you are writing a billing application, you might send an "Invoice Paid" notification to your users via the email and SMS channels.

<p align="left">
    <img src="http://5.189.150.145/21621947_10155695011058377_659334693.jpg" alt="Yii2 Notifications Module" />
</p>

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist webzop/yii2-notifications "*"
```

or add

```php
"webzop/yii2-notifications": "*"
```

to the require section of your `composer.json` file.

Usage
-----

Notifications is often used as an application module and configured in the application configuration like the following:

```php
[
    'modules' => [
        'notifications' => [
            'class' => 'webzop\notifications\Module',
            'channels' => [
                'screen' => [
                    'class' => 'webzop\notifications\channels\ScreenChannel',
                ],
                'email' => [
                    'class' => 'webzop\notifications\channels\EmailChannel',
                    'message' => [
                        'from' => 'example@email.com'
                    ],
                ],
            ],
        ],
    ],
]
```

### Create A Notification

Each notification is represented by a single class (typically stored in the  app/notifications directory).

```php
namespace app\notifications;

use Yii;
use webzop\notifications\Notification;

class AccountNotification extends Notification
{
    const KEY_NEW_ACCOUNT = 'new_account';

    const KEY_RESET_PASSWORD = 'reset_password';

    /**
     * @var \yii\web\User the user object
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function getTitle(){
        switch($this->key){
            case self::KEY_NEW_ACCOUNT:
                return Yii::t('app', 'New account {user} created', ['user' => '#'.$this->user->id]);
            case self::KEY_RESET_PASSWORD:
                return Yii::t('app', 'Instructions to reset the password');
        }
    }

    /**
     * @inheritdoc
     */
    public function getRoute(){
        return ['/users/edit', 'id' => $this->user->id];
    }
}
```

### Send A Notification

Once the notification is created, you can send it as following:

```php
$user = User::findOne(123);

AccountNotification::create(AccountNotification::KEY_RESET_PASSWORD, ['user' => $user])->send();
```



### Specifying Delivery Channels

Every notification class has a shouldSend($channel) method that determines on which type of keys and channels the notification will be delivered.
In this example, the notification will be delivered in all channels except "screen" or with key "new_account":

```php
/**
 * Get the notification's delivery channels.
 * @return boolean
 */
public function shouldSend($channel)
{
    if($channel->id == 'screen'){
        if(!in_array($this->key, [self::KEY_NEW_ACCOUNT])){
            return false;
        }
    }
    return true;
}
```

### Specifying The Send For Specific Channel
Every channel have a send method that receive a notification instance and define a way of that channel will send the notification. But you can override the send method by define toMail ("to" + [Channel ID]) in notification class. This example show how to do that:

```php
/**
 * Override send to email channel
 *
 * @param $channel the email channel
 * @return void
 */
public function toEmail($channel){
    switch($this->key){
        case self::KEY_NEW_ACCOUNT:
            $subject = 'Welcome to MySite';
            $template = 'newAccount';
            break;
        case self::KEY_RESET_PASSWORD:
            $subject = 'Password reset for MySite';
            $template = 'resetPassword';
            break;
    }

    $message = $channel->mailer->compose($template, [
        'user' => $this->user,
        'notification' => $this,
    ]);
    Yii::configure($message, $channel->message);

    $message->setTo($this->user->email);
    $message->setSubject($subject);
    $message->send($channel->mailer);
}
```

### Custom Channels

This module have a some pre-built channels, but you may want to write your own channels to deliver notifications. To do that you need define a class that contains a send method:

```php
namespace app\channels;

use webzop\notifications\Channel;
use webzop\notifications\Notification;

class VoiceChannel extends Channel
{
    /**
     * Send the given notification.
     *
     * @param Notification $notification
     * @return void
     */
    public function send(Notification $notification)
    {
        // use $notification->getTitle() ou $notification->getDescription();
        // Send your notification in this channel...
    }

}
```

You also should configure the channel in you application config:

```php
[
    'modules' => [
        'notifications' => [
            'class' => 'webzop\notifications\Module',
            'channels' => [
                'voice' => [
                    'class' => 'app\channels\VoiceChannel',
                ],
                //...
            ],
        ],
    ],
]
```

### Screen Channel

This channel is used to show small notifications as above image preview. The notifications will stored in database, so before using this channel, you have to run its migrations scripts:

```bash
./yii migrate/up --migrationPath=vendor/webzop/yii2-notifications/migrations/
```

So you can call the Notifications widget in your app layout to show generated notifications:

```html
<div class="header">
    ...
    <?php echo \webzop\notifications\widgets\Notifications::widget() ?>

</div>
```