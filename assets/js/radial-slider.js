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
                try {
                    if (this.options.isDragging) {
                        let $circle = $(this.element).find('.circle').eq(0),
                            touch = event.originalEvent.touches ? event.originalEvent.touches[0] : undefined,
                            center_x = $circle.outerWidth() / 2 + $circle.offset().left,
                            center_y = $circle.outerHeight() / 2 + $circle.offset().top,
                            pos_x = event.pageX || touch.pageX,
                            pos_y = event.pageY || touch.pageY,
                            delta_y = center_y - pos_y,
                            delta_x = center_x - pos_x,
                            angle = Math.atan2(delta_y, delta_x) * (180 / Math.PI) - 90;

                        if (angle < 0) {
                            angle += 360;
                        }
                        angle = Math.round(angle);
                        $(this.element).find('.dot').css('transform', 'rotate(' + angle + 'deg)');
                        this.displayTime(angle);
                    }
                } catch (e) {
                    //do nothing, touch error happened
                }
            }.bind(this));
        },

        /**
         * Shows time regarding on slider position and settings interval
         * @param {number} angle
         */
        displayTime: function (angle) {
            let min = $('.settings').find('.min-value.time').val(),
                max = $('.settings').find('.max-value.time').val(),
                percentage = angle / 360,
                time = percentage;
            $(this.element).closest('.timer-wrapper').find('.timer-button-container .time-value').html(angle + 'deg');


            // clearTimeout(saveCurrentTimeTimeout);
            // @TODO: watch how it works on the [CAF] and correct scopes for Interval

            // let saveCurrentTimeTimeout = setTimeout(function () {
            //     this.saveSelectedTime(time);
            // }.bind(this), 200);
        },

        /**
         * Save currently selected time to the local storage
         * @param {number} time
         */
        saveSelectedTime: function (time) {
            let state = JSON.parse(localStorage.getItem('state'));

            if (state !== null) {
                state.selectedTime = time;
            } else {
                state = {
                    selectedTime: time
                }
            }

            debugger;
            localStorage.state = JSON.stringify(state);
        }
    });
})(jQuery);
