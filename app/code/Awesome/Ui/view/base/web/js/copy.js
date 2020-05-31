;(function ($) {
    $.widget('awesome.copy', {
        options: {
            copyTextSelector: '',
            copyTriggerSelector: '',
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
            let $trigger = !this.options.copyTriggerSelector || $(this.element).is(this.options.copyTriggerSelector)
                ? $(this.element)
                : $(this.element).find(this.options.copyTriggerSelector);

            $trigger.on('click', this.copyText.bind(this));
        },

        /**
         * Copy text to the clipboard
         * @param {object} event
         */
        copyText: function (event) {
            event.preventDefault();
            let $temp = $('<input>'),
                $body = $('body'),
                $text = !this.options.copyTextSelector || $(this.element).is(this.options.copyTextSelector)
                    ? $(this.element)
                    : $(this.element).find(this.options.copyTextSelector);

            $body.append($temp);
            $temp.val($text.text()).select();
            document.execCommand('copy');
            $temp.remove();

            if (this.options.showMessage) {
                $(document).trigger('message.show', {
                    message: 'Copied to the clipboard!'
                });
            }
        }
    });
})(jQuery);
