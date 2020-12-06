define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict'

    $.widget('awesome.rangeSlider', {
        options: {
            difference: 1,
            maxValue: 100,
            minValue: 1,
        },

        activeController: null,
        container: null,
        dragging: false,
        maxController: null,
        maxInput: null,
        minController: null,
        minInput: null,

        /**
         * Constructor.
         */
        _create: function () {
            this.initFields();
            this.initValuesRestrictions();
            this.initBindings();
        },

        /**
         * Init widget fields.
         */
        initFields: function () {
            this.container = $(this.element).find('.range-controls');
            this.minController = this.container.find('.controller.min');
            this.maxController = this.container.find('.controller.max');
            this.minInput = $(this.element).find('.range-inputs .value.min');
            this.maxInput = $(this.element).find('.range-inputs .value.max');
        },

        /**
         * Init event listeners.
         */
        initBindings: function () {
            $(this.element).on('mousedown touchstart', '.controller', function (event) {
                if (!this.dragging) {
                    this.dragging = true;
                    this.activeController = $(event.currentTarget);

                    this.minController.css({'z-index': 4});
                    this.maxController.css({'z-index': 4});

                    this.activeController.css({'z-index': 5});
                }
            }.bind(this));

            $(document).on('mouseup touchend', function () {
                this.dragging = false;
                this.activeController = null;
            }.bind(this));

            $(document).on('mousemove touchmove', function (event) {
                if (this.dragging) {
                    try {
                        let touch = event.originalEvent.touches ? event.originalEvent.touches[0] : undefined,
                            pos = event.pageX || touch.pageX,
                            containerLeft = this.container.offset().left,
                            containerRight = containerLeft + this.container.innerWidth(),
                            newPos = (pos - containerLeft) / this.container.innerWidth() * 100;

                        if (pos < containerLeft) {
                            newPos = 0;
                        }
                        if (pos > containerRight) {
                            newPos = 100;
                        }

                        this.setControllerPosition(this.activeController, newPos, true);
                    } catch (e) {
                        // Do nothing, touch error happened
                    }
                }
            }.bind(this));

            this.minController.on('change', function () {
                let minValue = this.getValueFromController(this.minController),
                    maxValue = this.getValueFromController(this.maxController);

                if (minValue > maxValue - this.options.difference) {
                    this.setControllerPosition(this.maxController, minValue + this.options.difference);
                    this.maxInput.val(this.getValueFromController(this.maxController));

                    if (this.getValueFromController(this.maxController) === this.options.maxValue) {
                        this.setControllerPosition(this.minController, this.options.maxValue - this.options.difference);
                    }
                }
                this.minInput.val(this.getValueFromController(this.minController));
            }.bind(this));

            this.maxController.on('change', function () {
                let minValue = this.getValueFromController(this.minController),
                    maxValue = this.getValueFromController(this.maxController);

                if (maxValue < minValue + this.options.difference) {
                    this.setControllerPosition(this.minController, maxValue - this.options.difference);
                    this.minInput.val(this.getValueFromController(this.minController));

                    if ((this.getValueFromController(this.maxController) - this.options.difference) === this.options.minValue - 1) {
                        this.setControllerPosition(this.maxController, this.options.difference);
                    }
                }
                this.maxInput.val(this.getValueFromController(this.maxController));
            }.bind(this));

            this.minInput.on('change', function (event) {
                let newValue = parseInt($(event.target).val());

                if (!isNaN(newValue)) {
                    if (newValue <= this.options.maxValue - this.options.difference) {
                        this.setControllerPosition(this.minController, newValue);
                    } else {
                        let maxValue = parseInt(this.maxInput.val());
                        this.setControllerPosition(this.minController, this.options.maxValue - this.options.difference, false, false);

                        if (newValue > maxValue - this.options.difference) {
                            this.maxInput.val(newValue + this.options.difference);
                        }
                    }
                }
            }.bind(this));

            this.maxInput.on('change', function (event) {
                let newValue = parseInt($(event.target).val());

                if (!isNaN(newValue)) {
                    if (newValue <= this.options.maxValue) {
                        this.setControllerPosition(this.maxController, newValue);
                    } else {
                        let minValue = parseInt(this.minInput.val());
                        this.setControllerPosition(this.maxController, this.options.maxValue, false, false);

                        if (newValue < minValue + this.options.difference) {
                            this.minInput.val(newValue - this.options.difference);
                        }
                    }
                }
            }.bind(this));
        },

        /**
         * Set min/max values for inputs.
         */
        initValuesRestrictions: function () {
            this.minInput.attr('min', this.options.minValue);
            this.maxInput.attr('min', this.options.minValue + this.options.difference);
        },

        /**
         * Set position of controller
         * @param {jQuery} $rangeController
         * @param {number} value
         * @param {boolean} isPercentage
         * @param {boolean} trigger
         */
        setControllerPosition: function ($rangeController, value, isPercentage = false, trigger = true) {
            $rangeController.css({'left': (isPercentage ? value : this.valueToPercent(value)) + '%'});

            if (trigger) {
                $rangeController.trigger('change');
            }
        },

        /**
         * Get value by controller position.
         * @param {jQuery} $rangeController
         * @returns {number}
         */
        getValueFromController: function ($rangeController) {
            return this.percentToValue(parseFloat($rangeController.css('left')) / this.container.innerWidth() * 100)
        },

        /**
         * Convert percent to value.
         * @param {number} percent
         * @returns {number}
         */
        percentToValue: function (percent) {
            if (percent > 100) {
                percent = 100;
            }
            if (percent < 0) {
                percent = 0;
            }

            return Math.round(percent / 100 * (this.options.maxValue - this.options.minValue)) + this.options.minValue;
        },

        /**
         * Convert value to percent.
         * @param {number} value
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
});
