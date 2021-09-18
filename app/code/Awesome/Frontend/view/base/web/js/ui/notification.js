define([
    'jquery',
], function ($) {
    'use strict'

    const MAX_MESSAGE_NUMBER = 3;
    const TYPES_CLASS_MAP = {
        error: 'notification--error',
        success: 'notification--success',
    };

    return {
        /**
         * List of active messages, from newest to oldest.
         */
        messages: [],

        $notificationsWrapper: null,

        /**
         * Show informative message.
         * @param {string|array} message
         * @param {number} duration
         * @param {string} className
         */
        info: function (message, duration = 3000, className = '') {
            this._pushMessage(message, duration, className);
        },

        /**
         * Show success message.
         * @param {string} message
         * @param {number} duration
         * @param {string} className
         */
        success: function (message, duration = 3000, className = '') {
            this._pushMessage(message, duration, (className + ' ' + TYPES_CLASS_MAP.success).trim());
        },

        /**
         * Show error message.
         * @param {string|array} message
         * @param {number} duration
         * @param {string} className
         */
        error: function (message, duration = 3000, className = '') {
            this._pushMessage(message, duration, (className + ' ' + TYPES_CLASS_MAP.error).trim());
        },

        /**
         * Add message and show it.
         * If message is an array, every item is treated as a new line.
         * @param {string|array} message
         * @param {number} duration
         * @param {string} className
         * @private
         */
        _pushMessage: function (message, duration = 3000, className = '') {
            let $message = $(`<div class="notification"></div>`);
            let $content = $(`<div class="notification__content"></div>`);
            let content = Array.isArray(message) ? message : [message];

            content.forEach((paragraph) => $content.append($(`<p>${paragraph}</p>`)));
            $message.append($content);

            if (className) {
                $message.addClass(className);
            }

            if (this.messages.length >= MAX_MESSAGE_NUMBER) {
                this._removeMessage();
            }

            this.messages.push($message);
            this._getNotificationsWrapper().prepend($message);

            let messageCloseTimeout = setTimeout(() => {
                    $message.off('click.notification.close');
                    this._removeMessage($message);
                }, duration);

            $message.one('click.notification.close', () => {
                clearTimeout(messageCloseTimeout);
                this._removeMessage($message);
            });
        },

        /**
         * Prepare and return notifications wrapper element.
         * @returns {jQuery}
         * @private
         */
        _getNotificationsWrapper: function () {
            if (!this.$notificationsWrapper) {
                this.$notificationsWrapper = $('[data-notifications-wrapper]');

                if (this.$notificationsWrapper.length === 0) {
                    this.$notificationsWrapper = $(`<div class="notifications-wrapper" data-notifications-wrapper></div>`);

                    $('body').append(this.$notificationsWrapper);
                }
            }

            return this.$notificationsWrapper;
        },

        /**
         * Remove message from the list and container.
         * Last message will be removed if none provided.
         * @param {jQuery|null} $message
         * @private
         */
        _removeMessage: function ($message = null) {
            if ($message) {
                this.messages.splice(this.messages.indexOf($message), 1)
            } else {
                $message = this.messages.shift();
            }

            $message.fadeOut(300, () => $message.remove());
        },
    };
});
