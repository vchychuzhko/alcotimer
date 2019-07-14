;(function ($) {
    $.widget('awesome.base', {
        options: {
            menuSelector: '.menu',
            copyTextSelector: '.copy-on-click'
        },

        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            $('body').on('base.showMessage', this.showMessage.bind(this));
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            let $menu = $(this.options.menuSelector);

            $menu.on('click', '.toggle-container', function () {
                $menu.toggleClass('active');
            }.bind(this));

            $menu.on('close-menu', function () {
                $menu.removeClass('active');
            }.bind(this));

            $(this.options.copyTextSelector).on('click', this.copyText.bind(this));
        },

        /**
         * Copy text to the clipboard
         * @param {object} event
         */
        copyText: function (event) {
            event.preventDefault();
            let $temp = $('<input>'),
                $body = $('body');

            $body.append($temp);
            $temp.val($(event.target).text()).select();
            document.execCommand('copy');
            $temp.remove();

            $body.trigger('base.showMessage', {
                message: 'Copied to the clipboard!'
            });
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
