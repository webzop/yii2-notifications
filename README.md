# yii2-notifications
This module provides a notifications managing system for your yii2 application.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

So add

```
"webzop/yii2-notifications": "*"
```

to the require section of your `composer.json` file.

Usage
-----

Before using this module, you have to run its migrations scripts:

```bash
./yii migrate/up --migrationPath=vendor/webzop/yii2-notifications/migrations/
```

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
                ],
            ],
        ],
    ],
]
```

### Create a notification

```php

namespace app\notifications;

use Yii;

class AccountNotification extends \webzop\notifications\Notification
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

    /**
     * Specific send to email channel
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

    /**
     * @inheritdoc
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

}
```

### Send the notification
```php

$user = User::findOne(123);

AccountNotification::create(AccountNotification::KEY_RESET_PASSWORD, ['user' => $user])->send();

```
