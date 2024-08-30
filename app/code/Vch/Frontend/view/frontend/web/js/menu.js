define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict'

    $.widget('awesome.menu', {
        $menu: null,
        $control: null,
        $overlay: null,

        /**
         * Constructor
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
            this.$menu = $(this.element);

            this.$control = $('[data-menu-control]'); // @TODO: update selectors on rework
            this.$overlay = $('[data-menu-overlay]'); // @TODO: update selectors on rework
        },

        /**
         * Init event listeners
         * @private
         */
        _initBindings: function () {
            this.$control.on('click', () => this.toggle());

            this.$overlay.on('click', () => this.close());

            $(document).on('menu.open', () => this.open());

            $(document).on('menu.close', () => this.close());
        },

        /**
         * Toggle menu state.
         */
        toggle: function () {
            if (this.isOpened()) {
                this.close();
            } else {
                this.open();
            }
        },

        /**
         * Open menu.
         * @returns {boolean}
         */
        isOpened: function () {
            return this.$menu.hasClass('active');
        },

        /**
         * Open menu.
         */
        open: function () {
            this.$menu.addClass('active');
            this.$control.addClass('active');
        },

        /**
         * Open menu.
         */
        close: function () {
            this.$menu.removeClass('active');
            this.$control.removeClass('active');
        },
    });
});
