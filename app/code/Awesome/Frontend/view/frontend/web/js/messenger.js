define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict'

    const MAX_MESSAGE_NUMBER = 3;

    let messages = [];

    /**
     * Prepare and return messages container element.
     * @return {jQuery}
     */
    function getMessagesContainer () {
        let $messagesContainer = $('.messages-container');

        if ($messagesContainer.length !== 1) {
            $messagesContainer = $('<div class="messages-container"></div>');

            $('body').append($messagesContainer);
        }

        return $messagesContainer;
    }

    /**
     * Add message to the list and container.
     * @param {string} content
     * @param {number} duration
     * @param {boolean} error
     * @param {boolean} preprocessed
     */
    function pushMessage (content, duration = 3000, error = false, preprocessed = false) {
        let $message = $('<div class="message' + (error ? ' error' : '') + '"></div>'),
            $messagesContainer = getMessagesContainer();

        if (preprocessed) {
            $message.append(content);
        } else {
            $message.text(content);
        }

        if (messages.length >= MAX_MESSAGE_NUMBER) {
            removeMessage();
        }
        messages.push($message);
        $messagesContainer.prepend($message);

        $messagesContainer.addClass('slide-down');
        setTimeout(function () {
            $messagesContainer.removeClass('slide-down');
        }.bind(this), 400);

        let messageCloseTimeout = setTimeout(function () {
            $message.off('click.messageClose');
            removeMessage($message);
        }, duration);

        $message.one('click.messageClose', function () {
            clearTimeout(messageCloseTimeout);
            removeMessage($message);
        });
    }

    /**
     * Remove message from the list and container.
     * Last message will be removed if none provided.
     */
    function removeMessage ($message = null) {
        if ($message) {
            let index = messages.indexOf($message);

            messages.splice(index, 1)
        } else {
            $message = messages.shift();
        }

        $message.fadeOut(250, function () {
            $(this).remove();
        });
    }

    return {
        /**
         * Show informative message.
         * @param {string} message
         * @param {number} duration
         * @param {boolean} preprocessed
         */
        message: function (message, duration = 3000, preprocessed = false) {
            pushMessage(message, duration, false, preprocessed);
        },

        /**
         * Show error message.
         * @param {string} message
         * @param {number} duration
         * @param {boolean} preprocessed
         */
        error: function (message, duration = 3000, preprocessed = false) {
            pushMessage(message, duration, true, preprocessed);
        }
    };
});
