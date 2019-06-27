;(function ($) {
    $.widget('ava.base', {
        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            window.showMessage = this.showMessage.bind(this);
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $('.menu').on('click', '.toggle-container', function () {
                $('.menu').toggleClass('active');
            }.bind(this));

            $('.copy-on-click').on('click', this.copyText.bind(this));
        },

        /**
         * Copy text to the clipboard
         */
        copyText: function (event) {
            event.preventDefault();
            let $temp = $("<input>");

            $('body').append($temp);
            $temp.val($(event.target).text()).select();
            document.execCommand('copy');
            $temp.remove();

            window.showMessage('Copied to the clipboard!', 3000);
        },

        /**
         * Global function to show messages
         * @param message
         * @param duration
         * @param isError
         */
        showMessage: function (message, duration = 5000, isError = false) {
            let $message = $('<p class="message">' + message + '</p>'),
                $container =  $('<span class="message-container' + (isError ? ' error' : '') + '"></span>').append($message);

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
})(jQuery);
