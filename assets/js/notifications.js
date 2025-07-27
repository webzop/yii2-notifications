'use strict';

/**
 * notifications plugin
 */
const Notifications = (function (opts) {
    if (!opts.id) {
        throw new Error('Notifications: the param id is required.');
    }

    const elem = $('#' + opts.id);
    if (!elem.length) {
        throw Error('Notifications: the element was not found.');
    }

    const options = $.extend({
        pollInterval: 60000,
        xhrTimeout: 2000,
        readLabel: 'mark as unread',
        markAsReadLabel: 'mark as read'
    }, opts);

    /**
     * Renders a notification row.
     *
     * @param object The notification instance
     * @returns {jQuery|HTMLElement|*}
     */
    const renderRow = function (object) {
        const html = '<div href="#" class="dropdown-item notification-item' + (object.read != '0' ? ' read' : '') + '"' +
            ' data-id="' + object.id + '"' +
            ' data-class="' + object.class + '"' +
            ' data-key="' + object.key + '">' +
            '<span class="icon"></span> ' +
            '<span class="message">' + object.message + '</span>' +
            '<small class="timeago">' + object.timeago + '</small>' +
            '<span class="mark-read" data-toggle="tooltip" title="' + (object.read != '0' ? options.readLabel : options.markAsReadLabel) + '"></span>' +
            '</div>';
        return $(html);
    };

    /**
     * Initialise the "mark as read"/"mark as unread" buttons.
     */
    const initMarkReadButtons = function () {
        $('.notifications-list').find('.mark-read').off('click').on('click', function (e) {
            e.stopPropagation();
            const item = $(this).closest('.notification-item');
            let url = opts.readUrl;
            let callback = displayAsRead;
            if (item.hasClass('read')) {
                url = opts.unreadUrl;
                callback = displayAsUnread;
            }
            const mark = $(this);
            $.ajax({
                url: url,
                type: 'GET',
                data: {id: item.data('id')},
                dataType: 'json',
                timeout: opts.xhrTimeout,
                success: function (data) {
                    callback(mark);
                }
            });
        }).tooltip('dispose').tooltip();
    };

    /**
     * Render out the full list of notifications.
     */
    const showList = function () {
        let list = elem.find('.notifications-list');
        $.ajax({
            url: options.url,
            type: 'GET',
            dataType: 'json',
            timeout: opts.xhrTimeout,
            //loader: list.parent(),
            success: function (data) {
                let seen = 0;

                if ($.isEmptyObject(data.list)) {
                    list.find('.empty-row span').show();
                }

                $.each(data.list, function (index, object) {
                    if (list.find('>div[data-id="' + object.id + '"]').length) {
                        return;
                    }

                    let item = renderRow(object);

                    if (object.url) {
                        item.on('click', function (e) {
                            document.location = object.url;
                        });
                    }

                    if (object.seen == '0') {
                        seen += 1;
                    }

                    list.append(item);
                });

                initMarkReadButtons();

                setCount(seen, true);

                startPoll(true);
            }
        });
    };

    elem.find('> a[data-toggle="dropdown"]').on('click', function (e) {
        if (!$(this).parent().hasClass('show')) {
            showList();
        }
    });

    elem.find('.read-all').on('click', function (e) {
        e.stopPropagation();
        let link = $(this);
        $.ajax({
            url: options.readAllUrl,
            type: 'GET',
            dataType: 'json',
            timeout: opts.xhrTimeout,
            success: function (data) {
                displayAsRead(elem.find('.dropdown-item:not(.read)').find('.mark-read'));
                link.off('click').on('click', function () {
                    return false;
                });
                updateCount();
            }
        });
    });

    /**
     * Mark a notification as read.
     * @param mark
     */
    const displayAsRead = function (mark) {
        mark.attr('title', options.readLabel);
        mark.tooltip('dispose').tooltip();
        mark.closest('.notification-item').addClass('read');
    };

    /**
     * Mark a notification as unread.
     * @param mark
     */
    const displayAsUnread = function (mark) {
        mark.attr('title', options.markAsReadLabel);
        mark.tooltip('dispose').tooltip();
        mark.closest('.notification-item').removeClass('read');
    };

    const setCount = function (count, decrement) {
        const badge = elem.find('.notifications-count');
        if (decrement) {
            count = parseInt(badge.data('count')) - count;
        }

        if (count > 0) {
            badge.data('count', count).text(count).show();
        } else {
            badge.data('count', 0).text(0).hide();
        }
    };

    const updateCount = function () {
        $.ajax({
            url: options.countUrl,
            type: 'GET',
            dataType: 'json',
            timeout: opts.xhrTimeout,
            success: function (data) {
                setCount(data.count);
            },
            complete: function () {
                startPoll();
            }
        });
    };

    let _updateTimeout;
    const startPoll = function (restart) {
        if (restart && _updateTimeout) {
            clearTimeout(_updateTimeout);
        }
        _updateTimeout = setTimeout(function () {
            updateCount();
        }, opts.pollInterval);
    };

    // Fire the initial poll
    startPoll();
    initMarkReadButtons();
});