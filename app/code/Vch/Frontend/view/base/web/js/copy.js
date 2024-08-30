define([
    'jquery',
    'api/clipboard',
    'notification',
    'translator',
    'jquery/ui',
], function ($, clipboard, notification, __) {
    'use strict';

    $.widget('awesome.copy', {
        options: {
            preventDefault: false,
            showMessage: true,
        },

        $target: null,
        $trigger: null,

        isTextInput: false,

        /**
         * Constructor.
         */
        _create: function () {
            this._initFields();
            this._initBindings();
        },

        /**
         * Init widget fields.
         * @private
         */
        _initFields: function () {
            this.$trigger = $('[data-copy-trigger]', this.element).length
                ? $('[data-copy-trigger]', this.element)
                : $(this.element);

            this.$target = $('[data-copy-target]', this.element).length
                ? $('[data-copy-target]', this.element)
                : $(this.element);

            this.isTextInput = this.$target.is('input') || this.$target.is('textarea');
        },

        /**
         * Init event listeners.
         * @private
         */
        _initBindings: function () {
            this.$trigger.on('click', (event) => {
                if (this.options.preventDefault) {
                    event.preventDefault();
                }

                this.copy();
            });
        },

        /**
         * Copy target text to the clipboard.
         */
        copy: function () {
            let text;

            if (this.isTextInput) {
                text = this.$target.val();
                this.$target.select();
            } else {
                text = this.$target.text().trim();
            }

            clipboard.copy(text).then(() => {
                if (this.options.showMessage) {
                    notification.info(__('Copied to the clipboard'));
                }
            }, () => {
                console.error('Caller does not have permission to write to the clipboard');

                if (this.options.showMessage) {
                    notification.error(__('Clipboard is not available, try to copy manually'));
                }
            });
        },
    });
});
