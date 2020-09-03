'use strict';

var WebNotifications = (function(opts) {

    let service_worker_filepath = opts.serviceWorkerUrl;
    let service_worker_scope = opts.serviceWorkerScope;

    let SUBSCRIBE_URL = opts.subscribeUrl;
    let UNSUBSCRIBE_URL = opts.unsubscribeUrl;

    let VAPID_PUB_KEY = opts.vapid_pub_key;

    let subscribe_button_label = opts.subscribeLabel;
    let unsubscribe_button_label = opts.unsubscribeLabel;

    let subscribeButton;

    let isAccessGranted = false;
    let isSubscribed = false;


    window.addEventListener('load', function () {

        subscribeButton = document.querySelector('#js-web-push-subscribe-button');

        if (!checkBrowserSupportNotification()) {
            return;
        }

        registerServiceWorker()
            .then(initNotification);

    });


    /**
     * check browser support
     * @returns {boolean}
     */
    function checkBrowserSupportNotification() {
        if (!('serviceWorker' in navigator)) {
            console.warn('Service Worker isn\'t supported on this browser, disable or hide UI.');
            return false;
        }

        if (!('PushManager' in window)) {
            console.warn('Push isn\'t supported on this browser, disable or hide UI.');
            return false;
        }

        return true;
    }


    /**
     * register the service worker script
     * @returns {Promise<ServiceWorkerRegistration>}
     */
    function registerServiceWorker() {
        return navigator.serviceWorker.register(service_worker_filepath, {
            scope: service_worker_scope
        })
            .then(function (registration) {
                console.log('Service worker successfully registered. Scope (' + service_worker_scope + '): ' + registration.scope);
                return registration;
            })
            .catch(function (err) {
                console.error('Unable to register service worker.', err);
            });
    }


    /**
     * initialize notification
     */
    function initNotification() {

        // Notification.permission value can be 'granted', 'default', 'denied'
        // granted: user has accepted the request
        // default: user has dismissed the notification permission popup by clicking on x
        // denied: user has denied the request.
        isAccessGranted = (Notification.permission === 'granted');

        // We need the service worker registration to check for a subscription
        navigator.serviceWorker.ready.then(function (serviceWorkerRegistration) {
            // Do we already have a push message subscription?
            serviceWorkerRegistration.pushManager.getSubscription()
                .then(function (subscription) {

                    if (subscription) {

                        isSubscribed = true;

                        // Keep server sync with the latest subscription
                        sendSubscriptionToServer(subscription);
                    }

                    if(subscribeButton) {
                        updateButtonSubscribeStatus();
                    }
                })
                .catch(function (err) {
                    console.log('Error getting user subscription', err);
                });
        });

        // initialize page button for subscribe notification
        if(subscribeButton) {
            subscribeButton.addEventListener('click', function () {
                if (isSubscribed) {
                    unsubscribe();
                } else {
                    subscribe();
                }
            });

            // enable button
            subscribeButton.disabled = false;
        }
        else {
            if (!isSubscribed) {
                subscribe();
            }
        }

    }


    /**
     * update subscribe button status
     */
    function updateButtonSubscribeStatus() {
        if (isSubscribed) {
            subscribeButton.textContent = unsubscribe_button_label;
        } else {
            subscribeButton.textContent = subscribe_button_label;
        }
    }


    /**
     * manage unsubscribe request
     */
    function unsubscribe() {

        // Disable the button so it can't be changed while we process the permission request
        if(subscribeButton) {
            subscribeButton.disabled = true;
        }

        navigator.serviceWorker.ready.then(function (serviceWorkerRegistration) {
            // To unsubscribe from push messaging, you need get the subscription object, which you can call unsubscribe() on.
            serviceWorkerRegistration.pushManager.getSubscription().then(
                function (pushSubscription) {

                    // Check we have a subscription to unsubscribe
                    if (pushSubscription) {
                        // We have a subscription, so call unsubscribe on it
                        pushSubscription.unsubscribe()
                            .then(function () {

                            }).catch(function (e) {
                            // We failed to unsubscribe, this can lead to
                            // an unusual state, so may be best to remove
                            // the subscription id from your data store and
                            // inform the user that you disabled push

                            console.error('Unsubscription error: ', e);
                        });
                    }

                    // Make a request to your server to remove
                    // the users data from your data store so you
                    // don't attempt to send them push messages anymore

                    console.log('Successfully Unsubscribe');
                    isSubscribed = false;

                    if(subscribeButton) {
                        updateButtonSubscribeStatus();
                        subscribeButton.disabled = false;
                    }

                    return sendUnsubscriptionToServer(pushSubscription);

                }).catch(function (e) {
                console.log('Error thrown while un-subscribing from push messaging.', e);
            });

        });

    }


    /**
     * manage subscribe request
     */
    function subscribe() {

        // Disable the button so it can't be changed while we process the permission request
        if(subscribeButton) {
            subscribeButton.disabled = true;
        }

        navigator.serviceWorker.ready.then(function (serviceWorkerRegistration) {
            serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: VAPID_PUB_KEY
            })
                .then(function (pushSubscription) {

                    // The subscription was successful
                    console.log('Successfully Subscribe');
                    isSubscribed = true;

                    if(subscribeButton) {
                        updateButtonSubscribeStatus();
                        subscribeButton.disabled = false;
                    }

                    return sendSubscriptionToServer(pushSubscription);

                })
                .catch(function (e) {
                    if (Notification.permission === 'denied') {
                        // The user denied the notification permission which
                        // means we failed to subscribe and the user will need
                        // to manually change the notification permission to
                        // subscribe to push messages
                        console.log('Permission for Notifications was denied');
                    } else {
                        // A problem occurred with the subscription, this can
                        // often be down to an issue or lack of the gcm_sender_id
                        // and / or gcm_user_visible_only
                        console.log('Unable to subscribe to push.', e);
                    }
                });
        });

    }


    /**
     * send subscription to the server endpoint to save subscription details
     * @param subscription
     * @returns {Promise}
     */
    function sendSubscriptionToServer(subscription) {

        return fetch(SUBSCRIBE_URL, {
            method: 'POST',
            headers: new Headers({
                "Content-Type": "application/json"
            }),
            body: JSON.stringify(subscription),
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Bad status code from server.');
                }
                return response.json();
            })
            .then(function (responseData) {
                if (!(responseData && responseData.success)) {
                    throw new Error('Bad response from server.');
                }
            });
    }

    /**
     * send subscription to the server endpoint to remove subscription details
     * @param subscription
     * @returns {Promise}
     */
    function sendUnsubscriptionToServer(subscription) {

        return fetch(UNSUBSCRIBE_URL, {
            method: 'POST',
            headers: new Headers({
                "Content-Type": "application/json"
            }),
            body: JSON.stringify(subscription),
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Bad status code from server.');
                }
                return response.json();
            })
            .then(function (responseData) {
                if (!(responseData && responseData.success)) {
                    throw new Error('Bad response from server.');
                }
            });
    }

});

