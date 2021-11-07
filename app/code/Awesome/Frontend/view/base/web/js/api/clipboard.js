define([
    'notification',
    'translator',
], function (notification, __) {
    'use strict'

    return {
        /**
         * Copy text to clipboard.
         * @param {string} text
         * @param {boolean} showMessage
         */
        copy: function (text, showMessage = true) {
            navigator.clipboard.writeText(text).then(() => {
                if (showMessage) {
                    notification.info(__('Copied to the clipboard'));
                }
            }, () => {
                console.error('Caller does not have permission to write to the clipboard');

                if (showMessage) {
                    notification.error(__('Clipboard is not available, try to copy manually'));
                }
            });
        }
    };
});
