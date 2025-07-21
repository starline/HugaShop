/**
 * Edit lock utility
 * Automatically locks entity based on controller name
 *
 * @author Andri Huga
 * @version 1.2
 */

export function initEditLock(data) {

    function lock() {
        $.post(data.lock_url, { csrf: data.csrf });
    }

    lock();
    setInterval(lock, 300000);

    $(window).on('beforeunload', function () {
        navigator.sendBeacon(data.unlock_url, new URLSearchParams({ csrf: data.csrf }));
    });
}