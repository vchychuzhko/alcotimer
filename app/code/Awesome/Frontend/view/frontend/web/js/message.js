define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict'

    $.widget('awesome.message', {
        /**
         * Constructor
         */
        _create: function () {
            this.initBindings();
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(document).on('message.show', this.showMessage.bind(this));
        },

        /**
         * Global function to show messages
         * @param {object} event
         * @param {object} data
         * @property {string} data.message
         * @property {number} data.duration
         * @property {boolean} data.isError
         */
        showMessage: function (event, data) {
            let message = data.message,
                duration = data.duration ? data.duration : 3000,
                isError = data.isError ? data.isError : false,
                $message = $('<p class="message">' + message + '</p>'),
                $container =  $('<span class="message-container' + (isError ? ' error' : '') + '"></span>')
                    .append($message);
            //@TODO: rework to ready structure with only adding message (knockout?)

            $('body').append($container);

            $container.animate({'top': '60px'}, 200);

            let removeMessageTimeout = setTimeout(function () {
                $container.off();
                $container.fadeOut(200, 'linear', function () {
                    $container.remove();
                }.bind(this));
            }.bind(this), duration);

            $container.on('click', function () {
                clearTimeout(removeMessageTimeout);
                $container.off();
                $container.remove();
            }.bind(this));
        }
    });
});
