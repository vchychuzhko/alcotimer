;(function ($) {
    $.widget('awesome.base', {
        options: {
            copyTextSelector: '.copy-on-click',
            showMessage: 1
        },

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

            if (this.showMessage) {
                $body.trigger('message.showMessage', {
                    message: 'Copied to the clipboard!'
                });
            }
        }
    });
})(jQuery);
