;(function ($) {
    $.widget('ava.radialSlider', {
        options: {
            isDragging: false
        },

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
            $(this.element).on('mousedown touchstart', '.circle', function () {
                this.options.isDragging = true;
            }.bind(this));

            $(document).on('mouseup touchend', function () {
                this.options.isDragging = false;
            }.bind(this));

            $(window).on('mousemove touchmove', function (event) {
                if (this.options.isDragging) {
                    let circle = $(this.element).find('.circle'),
                        touch = event.originalEvent.touches ? event.originalEvent.touches[0] : undefined,
                        center_x = ($(circle).outerWidth() / 2) + $(circle).offset().left,
                        center_y = ($(circle).outerHeight() / 2) + $(circle).offset().top,
                        pos_x = event.pageX || touch.pageX,
                        pos_y = event.pageY || touch.pageY,
                        delta_y = center_y - pos_y,
                        delta_x = center_x - pos_x,
                        angle = Math.atan2(delta_y, delta_x) * (180 / Math.PI); // Calculate Angle between circle center and mouse pos
                    angle -= 90;

                    if (angle < 0) {
                        angle = 360 + angle // Always show angle positive
                    }
                    angle = Math.round(angle);
                    $(this.element).find('.dot').css("transform", "rotate(" + angle + "deg)");
                    $(this.element).closest('.timer-wrapper').find('.timer-button-container .time-value').html(angle + 'deg');
                }
            }.bind(this));
        }
    });
})(jQuery);
