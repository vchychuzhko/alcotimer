define([
    'jquery',
], function ($) {
    'use strict'

    const MAX_MESSAGE_NUMBER = 3;

    return {
        /**
         * List of active messages, from newest to oldest.
         */
        messages: [],

        /**
         * Show informative message.
         * @param {string} message
         * @param {number} duration
         * @param {boolean} preprocessed
         */
        info: function (message, duration = 3000, preprocessed = false) {
            this._pushMessage(message, duration, false, preprocessed);
        },

        /**
         * Show error message.
         * @param {string} message
         * @param {number} duration
         * @param {boolean} preprocessed
         */
        error: function (message, duration = 3000, preprocessed = false) {
            this._pushMessage(message, duration, true, preprocessed);
        },

        /**
         * Add message to the list and container.
         * @param {string} content
         * @param {number} duration
         * @param {boolean} error
         * @param {boolean} preprocessed
         * @private
         */
        _pushMessage: function (content, duration = 3000, error = false, preprocessed = false) {
            let $message = $(`<div class="message"></div>`);

            if (preprocessed) {
                $message.append(content);
            } else {
                $message.text(content);
            }

            if (error) {
                $message.addClass('message--error');
            }

            if (this.messages.length >= MAX_MESSAGE_NUMBER) {
                this._removeMessage();
            }

            this.messages.push($message);
            this._getMessagesWrapper().prepend($message);

            let messageCloseTimeout = setTimeout(() => {
                    $message.off('click.message.close');
                    this._removeMessage($message);
                }, duration);

            $message.one('click.message.close', () => {
                clearTimeout(messageCloseTimeout);
                this._removeMessage($message);
            });
        },

        /**
         * Prepare and return messages wrapper element.
         * @returns {jQuery}
         * @private
         */
        _getMessagesWrapper: function () {
            let $messagesWrapper = $('[data-message-wrapper]');

            if ($messagesWrapper.length === 0) {
                $messagesWrapper = $(`<div class="messages-wrapper" data-message-wrapper></div>`);

                $('body').append($messagesWrapper);
            }

            return $messagesWrapper;
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

            $message.fadeOut(300, function () {
                $(this).remove();
            });
        },
    };
});
