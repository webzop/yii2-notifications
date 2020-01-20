<?php

namespace webzop\notifications\model;

use Minishlink\WebPush\Subscription;

interface WebNotificationRecipient {

    /**
     * @return Subscription[]
     */
    public function getSubscriptions();


/*
    public function getSubscriptionsExample() {

    // array of notifications
        public function example() {

            // array of notifications
            $notifications = [
                [
                    'subscription' => \Minishlink\WebPush\Subscription::create([
                        'endpoint' => 'https://updates.push.services.mozilla.com/push/abc...', // Firefox 43+,
                        'publicKey' => 'BPcMbnWQL5GOYX/5LKZXT6sLmHiMsJSiEvIFvfcDvX7IZ9qqtq68onpTPEYmyxSQNiH7UD/98AUcQ12kBoxz/0s=', // base 64 encoded, should be 88 chars
                        'authToken' => 'CxVX6QsVToEGEcjfYPqXQw==', // base 64 encoded, should be 24 chars
                    ]),
                    'payload' => 'hello !',
                ], [
                    'subscription' => \Minishlink\WebPush\Subscription::create([
                        'endpoint' => 'https://android.googleapis.com/gcm/send/abcdef...', // Chrome
                    ]),
                    'payload' => null,
                ], [
                    'subscription' => \Minishlink\WebPush\Subscription::create([
                        'endpoint' => 'https://example.com/other/endpoint/of/another/vendor/abcdef...',
                        'publicKey' => '(stringOf88Chars)',
                        'authToken' => '(stringOf24Chars)',
                        'contentEncoding' => 'aesgcm', // one of PushManager.supportedContentEncodings
                    ]),
                    'payload' => '{msg:"test"}',
                ], [
                    'subscription' => \Minishlink\WebPush\Subscription::create([ // this is the structure for the working draft from october 2018 (https://www.w3.org/TR/2018/WD-push-api-20181026/)
                         "endpoint" => "https://example.com/other/endpoint/of/another/vendor/abcdef...",
                         "keys" => [
                             'p256dh' => '(stringOf88Chars)',
                             'auth' => '(stringOf24Chars)'
                         ],
                    ]),
                    'payload' => '{"msg":"Hello World!"}',
                ],
            ];

        }

    }
*/
}

?>
