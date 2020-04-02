# yii2-notifications
This module provides a way to sending notifications across a variety of delivery channels, including mail, screen, SMS (via Nexmo), etc. Notifications may also be stored in a database so they may be displayed in your web interface.

Notifications are short messages that notify users of something that occurred in your application. For example, if you are writing a billing application, you might send an "Invoice Paid" notification to your users via the email and SMS channels.

<p align="left">
    <img src="http://5.189.150.145/21621947_10155695011058377_659334693.jpg" alt="Yii2 Notifications Module" />
</p>

Requirements
------------

- PHP 7.1+
    - gmp
    - mbstring
    - curl
    - openssl
    
- PHP 7.2+ is recommended for better performance.

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
                'web' => [
                    'class' => 'webzop\notifications\channels\WebChannel',
                    'enable' => true,                                       // OPTIONAL (default: true) enable/disable web channel
                    'config' => [
                        'serviceWorkerFilepath' => '/service-worker.js',    // OPTIONAL (default: /service-worker.js) is the service worker filename
                        'serviceWorkerScope' => '/app',                     // OPTIONAL (default: './' the service worker path) the scope of the service worker: https://developers.google.com/web/ilt/pwa/introduction-to-service-worker#registration_and_scope
                        'serviceWorkerUrl' => 'url-to-serviceworker',       // OPTIONAL (default: Url::to(['/notifications/web-push-notification/service-worker']))
                        'subscribeUrl' => 'url-to-subscribe-handler',       // OPTIONAL (default: Url::to(['/notifications/web-push-notification/subscribe']))
                        'unsubscribeUrl' => 'url-to-unsubscribe-handler',   // OPTIONAL (default: Url::to(['/notifications/web-push-notification/unsubscribe']))
                        'subscribeLabel' => 'subscribe button label',       // OPTIONAL (default: 'Subscribe')
                        'unsubscribeLabel' => 'subscribe button label',     // OPTIONAL (default: 'Unsubscribe')
                    ],
                    'auth' => [
                        'VAPID' => [
                            'subject' => 'mailto:me@website.com',           // can be a mailto: or your website address
                            'publicKey' => '~88 chars',                     // (recommended) uncompressed public key P-256 encoded in Base64-URL
                            'privateKey' => '~44 chars',                    // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
                            'pemFile' => 'path/to/pem',                     // if you have a PEM file and can link to it on your filesystem
                            'pem' => 'pemFileContent',                      // if you have a PEM file and want to hardcode its content
                            'reuseVAPIDHeaders' => true                     // OPTIONAL (default: true) you can reuse the same JWT token them for the same flush session to boost performance using
                        ],
                    ],
                ],
            ],
        ],
    ],
];
```

To enable Web Push Notifications browsers is needed to verify your identity. A standard called VAPID can authenticate the application for all browsers. You'll need to create and provide a public and private key for your server. These keys must be safely stored and should not change.

If you want to disable the Web Push Notifications simply set the flag 'notifications.web.enable' to false.

In order to generate the uncompressed public and secret key, encoded in Base64, enter the following in your Linux bash:

```
$ openssl ecparam -genkey -name prime256v1 -out private_key.pem
$ openssl ec -in private_key.pem -pubout -outform DER|tail -c 65|base64|tr -d '=' |tr '/+' '_-' >> public_key.txt
$ openssl ec -in private_key.pem -outform DER|tail -c +8|head -c 32|base64|tr -d '=' |tr '/+' '_-' >> private_key.txt
```

Or you can use this method provided in the module:

```php
\Minishlink\WebPush\VAPID::createVapidKeys();
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


### Web Push Notification Channel

This channel is used to send web push notification to subscriber. Each notification subscription will be stored in the database, so before using this channel, you have to run its migrations scripts:

```bash
./yii migrate/up --migrationPath=vendor/webzop/yii2-notifications/migrations/
```

So you can call the Notifications widget in your app layout to show generated notifications:

```html
<div>
    <?php echo \webzop\notifications\widgets\WebNotifications::widget() ?>
</div>
```

You can customize the HTML template for the widget as follow. 
Setting template to false will hide all widget HTML and the browser will prompts the user to allow notifications.
If you customize the HTML template remember to include a button with id 'js-web-push-subscribe-button':

```html
<div>
    <?= \webzop\notifications\widgets\WebNotifications::widget([
        'template' => '... <button id="js-web-push-subscribe-button" disabled="disabled"></button> ...'
    ]) ?>
</div>
```

Remember to place the service-worker.js file in the web root in order to serve the service worker when the WebNotifications widget is initialized.