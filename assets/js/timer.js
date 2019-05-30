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
            $(this.element).on('click', '.random-button', this.setRandom.bind(this));
        },

        /**
         * Start/stop the timer
         */
        toggleTimer: function () {
            $(this.element).toggleClass('in-progress');
        },

        /**
         * Stop current timer and set random time
         */
        setRandom: function () {
            console.log('random');
        },
    });
})(jQuery);
