define([], function () {
    'use strict'

    return {
        /**
         * Get local storage record by root name.
         * @param {string} root
         * @returns {Object}
         */
        get: function (root) {
            let data = localStorage.getItem(root) || '{}';

            return JSON.parse(data);
        },

        /**
         * Set local storage record by root name.
         * @param {string} root
         * @param {*} data
         */
        set: function (root, data) {
            localStorage.setItem(root, JSON.stringify(data));
        },
    };
});
