;(function ($) {
    $.widget('ava.rangeSlider', {
        options: {
            containerWidth: 0,
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
            $(window).trigger('resize');
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            let $minRange = $(this.element).find('.min-controller'),
                $maxRange = $(this.element).find('.max-controller'),
                $minInput = $(this.element).find('.min-value'),
                $maxInput = $(this.element).find('.max-value');

            $(window).on('resize', function () {
                let $container =  $(this.element).find('.range-controls');

                this.options.containerWidth = $container.innerWidth();
            }.bind(this));

            $(this.element).on('mousedown touchstart', '.range-controls', function (event) {
                this.options.isDragging = true;

                $.each([$minRange, $maxRange], function (index, controller) {
                    $(controller).css({'z-index': 4});
                });
                $(event.target).css({'z-index': 5});
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
                            $container =  $(this.element).find('.range-controls'),
                            containerLeft = $container.offset().left,
                            containerRight = containerLeft + this.options.containerWidth,
                            newPos = (pos - containerLeft) / this.options.containerWidth * 100;

                        if (pos < containerLeft) {
                            newPos = 0;
                        }

                        if (pos > containerRight) {
                            newPos = 100;
                        }

                        this.setControllerPosition($event, newPos, true);
                    }
                } catch (e) {
                    //do nothing, touch error happened
                }
            }.bind(this));

            $minRange.on('change', function () {
                let minValue = this.getValueFromController($minRange),
                    maxValue = this.getValueFromController($maxRange);

                if (minValue > maxValue - this.options.difference) {
                    this.setControllerPosition($maxRange, minValue + this.options.difference);
                    $maxInput.val(this.getValueFromController($maxRange));

                    if (this.getValueFromController($maxRange) === this.options.maxValue) {
                        this.setControllerPosition($minRange, this.options.maxValue - this.options.difference);
                    }
                }
                $minInput.val(this.getValueFromController($minRange));
            }.bind(this));

            $maxRange.on('change', function () {
                let minValue = this.getValueFromController($minRange),
                    maxValue = this.getValueFromController($maxRange);

                if (maxValue < minValue + this.options.difference) {
                    this.setControllerPosition($minRange, maxValue - this.options.difference);
                    $minInput.val(this.getValueFromController($minRange));

                    if ((this.getValueFromController($maxRange) - this.options.difference) === this.options.minValue - 1) {
                        this.setControllerPosition($maxRange, this.options.difference);
                    }
                }
                $maxInput.val(this.getValueFromController($maxRange));
            }.bind(this));

            $minInput.on('change', function (event) {
                let newValue = parseInt($(event.target).val());

                if (!isNaN(newValue)) {
                    if (newValue <= this.options.maxValue - this.options.difference) {
                        this.setControllerPosition($minRange, newValue);
                    } else {
                        let maxValue = parseInt($maxInput.val());
                        this.setControllerPosition($minRange, this.options.maxValue - this.options.difference, false, false);

                        if (newValue > maxValue - this.options.difference) {
                            $maxInput.val(newValue + this.options.difference);
                        }
                    }
                }
            }.bind(this));

            $maxInput.on('change', function (event) {
                let newValue = parseInt($(event.target).val());

                if (!isNaN(newValue)) {
                    if (newValue <= this.options.maxValue) {
                        this.setControllerPosition($maxRange, newValue);
                    } else {
                        let minValue = parseInt($minInput.val());
                        this.setControllerPosition($maxRange, this.options.maxValue, false, false);

                        if (newValue < minValue + this.options.difference) {
                            $minInput.val(newValue - this.options.difference);
                        }
                    }
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
         * @param isPercentage
         * @param trigger
         */
        setControllerPosition: function ($rangeController, position, isPercentage = false, trigger = true) {
            let left = position;

            if (!isPercentage) {
                left = this.valueToPercent(position);
            }
            $rangeController.css({'left': left + '%'});

            if (trigger) {
                $rangeController.trigger('change');
            }
        },

        /**
         * Set position of controller
         * @param $rangeController
         * @returns {number}
         */
        getValueFromController: function ($rangeController) {
            return this.percentToValue(parseFloat($rangeController.css('left')) / this.options.containerWidth * 100)
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
            if (value > this.options.maxValue) {
                value = this.options.maxValue;
            }

            if (value < this.options.minValue) {
                value = this.options.minValue;
            }

            return (value - this.options.minValue) / (this.options.maxValue - this.options.minValue) * 100;
        }
    });
})(jQuery);
