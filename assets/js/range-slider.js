;(function ($) {
    $.widget('ava.rangeSlider', {
        options: {
            difference: 2,
            isDragging: false,
            minValue: 5,
            maxValue: 20
        },

        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initValuesRestrictions();
            this.initBindings();
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            let $container =  $(this.element).find('.range-controls'),
                $minRange = $container.find('.min-range'),
                $maxRange = $container.find('.max-range'),
                $minInput = $(this.element).find('.min-value'),
                $maxInput = $(this.element).find('.max-value'),
                containerWidth = $container.innerWidth();

            $(this.element).on('mousedown touchstart', '.range-controls', function () {
                this.options.isDragging = true;
            }.bind(this));

            $(document).on('mouseup touchend', function () {
                this.options.isDragging = false;
            }.bind(this));

            $(this.element).on('mousemove touchmove', function (event) {
                try {
                    if (this.options.isDragging) {
                        let touch = event.originalEvent.touches ? event.originalEvent.touches[0] : undefined,
                            pos = event.pageX || touch.pageX,
                            $event = $(event.target),
                            containerLeft = $container.offset().left,
                            containerRight = containerLeft + containerWidth,
                            newPos = (pos - containerLeft) / containerWidth * 100;

                        if (pos < containerLeft) {
                            newPos = 0;
                        }

                        if (pos > containerRight) {
                            newPos = 100;
                        }

                        this.setControllerPosition($event, newPos);
                    }
                } catch (e) {
                    //do nothing, touch error happened
                }
            }.bind(this));

            $minRange.on('change', function () {
                let minValue = this.percentToValue(parseFloat($minRange.css('left')) / containerWidth * 100),
                    maxValue = this.percentToValue(parseFloat($maxRange.css('left')) / containerWidth * 100);

                if (minValue > maxValue - this.options.difference) {
                    this.setControllerPosition($maxRange, minValue + this.options.difference);

                    if (maxValue === this.options.maxValue) {
                        this.setControllerPosition($minRange, this.options.maxValue - this.options.difference);
                    }
                }
                $minInput.val(minValue);
                $maxInput.val(maxValue);
            }.bind(this));

            $maxRange.on('change', function () {
                let minValue = this.percentToValue(parseFloat($minRange.css('left')) / containerWidth * 100),
                    maxValue = this.percentToValue(parseFloat($maxRange.css('left')) / containerWidth * 100);

                if (maxValue < minValue + this.options.difference) {
                    this.setControllerPosition($minRange, maxValue - this.options.difference);

                    if ((maxValue - this.options.difference) === this.options.minValue) {
                        this.setControllerPosition($maxRange, this.options.difference);
                    }
                }
                $minInput.val(minValue);
                $maxInput.val(maxValue);
            }.bind(this));

            $minInput.on('change', function (event) {
                let newValue = parseInt($(event.target).val());

                if (newValue <= this.options.maxValue) {
                    this.setControllerPosition($minRange, newValue);
                }
            }.bind(this));

            $maxInput.on('change', function (event) {
                let newValue = parseInt($(event.target).val());

                if (newValue <= this.options.maxValue) {
                    this.setControllerPosition($maxRange, newValue);
                }
            }.bind(this));
        },

        /**
         * Retrieve minimal values for slider
         */
        initValuesRestrictions: function () {
            $(this.element).find('.range-inputs .min-value').attr('min', this.options.minValue);
            $(this.element).find('.range-inputs .max-value').attr('min', this.options.minValue + this.options.difference);
        },

        /**
         * Set position of controller
         * @param $rangeController
         * @param position
         */
        setControllerPosition: function ($rangeController, position) {
            $rangeController.css({'left': this.valueToPercent(position) + '%'});
            $rangeController.trigger('change');
        },

        /**
         * Convert percent to value
         * @param percent
         * @returns {number}
         */
        percentToValue: function (percent) {
            return Math.round(percent / 100 * (this.options.maxValue - this.options.minValue)) + this.options.minValue;
        },

        /**
         * Convert value to percent
         * @param value
         * @returns {number}
         */
        valueToPercent: function (value) {
            return (value - this.options.minValue) / (this.options.maxValue - this.options.minValue) * 100;
        }
    });
})(jQuery);
