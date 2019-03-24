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
            let $toggleContainer = $(this.element).find('.toggle-container');

            $toggleContainer.on('click', function () {
                $(this.element).toggleClass('active');
            }.bind(this));
        }
    });
})(jQuery);
