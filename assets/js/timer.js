;(function ($) {
    $.widget('ava.timer', {
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
            $(this.element).on('click', '.timer-button', this.toggleTimer.bind(this));
        },

        /**
         * Start/stop the timer
         */
        toggleTimer: function () {
            console.log('click');
        },
    });
})(jQuery);
