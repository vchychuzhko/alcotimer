;(function ($) {
    $.widget('vlad.menu', {
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
            $(this.element).find('.toggle-container').on('click', function () {
                $(this.element).toggleClass('active');
            }.bind(this))
        }
    });
})(jQuery);
