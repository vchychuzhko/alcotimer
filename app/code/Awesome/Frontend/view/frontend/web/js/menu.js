define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict'

    $.widget('awesome.menu', {
        options: {
            menuSelector: '.menu',
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
            let $menu = $(this.options.menuSelector);

            $('.toggle-container, .menu-mobile-overlay').on('click', function () {
                $menu.toggleClass('active');
            }.bind(this));

            $(document).on('menu.open', function () {
                $menu.addClass('active');
            }.bind(this));

            $(document).on('menu.close', function () {
                $menu.removeClass('active');
            }.bind(this));
        },
    });
});
