/**
 * The service worker both receives the push message and creates the notification.
 * When a browser that supports push messages receives a message, it sends a push event to the service worker.
 * We can create a push event listener in the service worker to handle the message.
 */

/**
 * Notification events:
 * - notificationclose:         user dismiss the notification clicking on the close button or swiping it
 * - notificationclick:         the user click somewhere on the notification and the propery 'action' allow to know which button has been clicked
 * - push:                      server send a notification
 */


/**
 * when user click the notification the browser focus the web page already opened, otherwise opens a new window
 */
self.addEventListener('notificationclick', function (event) {
    event.waitUntil(
        self.clients.matchAll().then(function (clientList) {
            if (clientList.length > 0) {
                return clientList[0].focus();
            }

            let url = new URL(self.location.origin).href;
            return self.clients.openWindow(url);
        })
    );
});


self.addEventListener('push', function(e) {

    console.log('push notification recived');

    // handle data from push server
    let title = 'Web Push Notification';
    let options;

    if (e.data) {

        let push_data = e.data.json();
        console.log(push_data);

        options = push_data;
        options.timestamp = Math.floor(Date.now());

        if(push_data.title) {
            title = push_data.title;
        }
    }

    e.waitUntil(
        self.registration.showNotification(title, options)
    );
});
