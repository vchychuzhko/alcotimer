define([
    'jquery',
    'messenger',
    'jquery/ui',
], function ($, messenger) {
    'use strict'

    $.widget('awesome.copy', {
        options: {
            showMessage: 1,
            target: '',
            trigger: '',
        },

        /**
         * Constructor.
         */
        _create: function () {
            this.initBindings();
        },

        /**
         * Init event listeners.
         */
        initBindings: function () {
            let $trigger = !this.options.trigger || $(this.element).is(this.options.trigger)
                    ? $(this.element)
                    : $(this.options.trigger),
                $target = !this.options.target || $(this.element).is(this.options.target)
                    ? $(this.element)
                    : $(this.options.target)

            $trigger.on('click', this.copyText.bind(this, $target));
        },

        /**
         * Copy text to the clipboard.
         * @param {jQuery} $target
         * @param {Object} event
         */
        copyText: function ($target, event) {
            event.preventDefault();

            let $temp = $('<input>');

            $('body').append($temp);
            $temp.val($target.text()).select();
            document.execCommand('copy');
            $temp.remove();

            if (this.options.showMessage) {
                messenger.message('Copied to the clipboard!');
            }
        }
    });
});
