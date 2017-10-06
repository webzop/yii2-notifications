<?php

namespace webzop\notifications;

/**
 * notifications module bootstrap class.
 */
class Bootstrap implements \yii\base\BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        // add module I18N category
        if (!isset($app->i18n->translations['modules/notifications/*'])) {
            $app->i18n->translations['modules/notifications*'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@webzop/notifications/messages',
            ];
        }
    }
}
