;(function ($) {
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

            $menu.on('menu.closeMenu', function () {
                $menu.removeClass('active');
            }.bind(this));
        },
    });
})(jQuery);
