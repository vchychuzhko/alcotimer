;(function ($) {
    $.widget('ava.menu', {
        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(this.element).on('click', '.toggle-container', function () {
                $(this.element).toggleClass('active');
            }.bind(this));
        }
    });
})(jQuery);
