define([], function () {
    'use strict';

    return {
        /**
         * Copy text to clipboard.
         * @param {string} text
         * @returns Promise
         */
        copy: function (text) {
            return navigator.clipboard.writeText(text);
        },
    };
});
