;(function ($) {
    $.widget('awesome.menu', {
        options: {
            menuSelector: '.menu',
        },

        /**
         * Constructor
         */
        _create: function () {
            this.addMobileOverlay();
            this.initBindings();
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            let $menu = $(this.options.menuSelector);

            $('header').on('click', '.toggle-container', function () {
                $menu.toggleClass('active');
            }.bind(this));

            $('.menu-mobile-overlay').on('click', function () {
                $('.toggle-container').trigger('click');
            }.bind(this));

            $menu.on('menu.closeMenu', function () {
                $menu.removeClass('active');
            }.bind(this));
        },

        addMobileOverlay: function () {
            $('.content').append('<div class="menu-mobile-overlay"></div>');
        }
    });
})(jQuery);
