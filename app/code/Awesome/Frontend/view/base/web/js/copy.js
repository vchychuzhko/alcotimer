define([
    'jquery',
    'notification',
    'translator',
    'jquery/ui',
], function ($, notification, __) {
    'use strict'

    /**
     * Copy text to clipboard.
     * @param {string} text
     * @param {boolean} showMessage
     */
    const copy = function (text, showMessage = true) {
        navigator.clipboard.writeText(text).then(() => {
            if (showMessage) {
                notification.info(__('Copied to the clipboard'));
            }
        }, () => {
            console.error('Caller does not have permission to write to the clipboard.');
        });
    }

    $.widget('awesome.copy', {
        options: {
            showMessage: true,
        },

        $target: null,
        $trigger: null,

        isInput: false,

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

            this.isInput = this.$target.is('input') || this.$target.is('textarea');
        },

        /**
         * Init event listeners.
         * @private
         */
        _initBindings: function () {
            this.$trigger.on('click', () => this.copy());
        },

        /**
         * Copy target text to the clipboard.
         */
        copy: function () {
            let text;

            if (this.isInput) {
                text = this.$target.val();
                this.$target.select();
            } else {
                text = this.$target.text().trim();
            }

            copy(text, this.options.showMessage);
        },
    });

    return copy;
});
