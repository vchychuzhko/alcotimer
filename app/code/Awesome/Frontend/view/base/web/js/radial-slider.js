define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict'

    const MAX_ANGLE = 360,
          MIN_ANGLE = 0,
          STEP_INDICATOR_ANGLE = 30;

    $.widget('awesome.radialSlider', {
        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            this.updateCircleParameters();
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(window).on('resize', this.updateCircleParameters.bind(this));

            $(this.element).on('mousedown touchstart', '.radial-slider .radial-controller', function () {
                //Use '.radial-slider' as 2nd parameter to have "teleport" effect on moving out of controller dot
                this.isDragging = true;
            }.bind(this));

            $(document).on('mouseup touchend', function () {
                this.isDragging = false;
            }.bind(this));

            $(window).on('mousemove touchmove', function (event) {
                if (this.isDragging) {
                    try {
                        let touch = event.originalEvent.touches ? event.originalEvent.touches[0] : undefined,
                            targetX = (event.pageX || touch.pageX) - this.offsetLeft - this.borderWidth / 2,
                            targetY = (event.pageY || touch.pageY) - this.offsetTop - this.borderWidth / 2,
                            angle = this.getAngleByCoordinates(targetX, targetY),
                            previousAngle = this.getValueFromController();

                        if (this.minReached) {
                            if (targetX - this.centerX < 0) {
                                angle = MIN_ANGLE;
                            } else {
                                this.minReached = false;
                            }
                        } else if (this.maxReached) {
                            if (targetX - this.centerX > 0) {
                                angle = MAX_ANGLE;
                            } else {
                                this.maxReached = false;
                            }
                        } else if (angle >= MAX_ANGLE - STEP_INDICATOR_ANGLE
                            || angle <= MIN_ANGLE + STEP_INDICATOR_ANGLE
                        ) {
                            if (previousAngle >= MAX_ANGLE - STEP_INDICATOR_ANGLE
                                && angle <= MIN_ANGLE + STEP_INDICATOR_ANGLE
                            ) {
                                angle = MAX_ANGLE;
                                this.maxReached = true;
                            } else if (previousAngle <= MIN_ANGLE + STEP_INDICATOR_ANGLE
                                && angle >= MAX_ANGLE - STEP_INDICATOR_ANGLE
                            ) {
                                angle = MIN_ANGLE;
                                this.minReached = true;
                            }
                        }

                        this.setControllerPosition(angle);
                    } catch (e) {
                        //do nothing, touch error happened
                    }
                }
            }.bind(this));

            $(this.element).on('radial-slider.percentageSet', this.updateSlider.bind(this));
        },

        /**
         * Calculate and update radial circle parameters
         */
        updateCircleParameters: function () {
            let $circle = $(this.element).find('.radial-slider');

            this.borderWidth = parseFloat($circle.css('border-left-width'));
            this.offsetLeft = $circle.offset().left;
            this.offsetTop = $circle.offset().top;
            this.centerX = ($circle.outerWidth() - this.borderWidth) / 2;
            this.centerY = ($circle.outerHeight() - this.borderWidth) / 2;
            this.radius = ($circle.outerWidth() - this.borderWidth) / 2;

            this.setControllerPosition(this.getValueFromController(), false, false);
        },

        /**
         * Update percentage value regarding slider position
         * @param {number} angle
         */
        updatePercentage: function (angle) {
            let percentage = angle / MAX_ANGLE * 100;

            $(this.element).data('percentage', percentage.toString())
                .trigger('radial-slider.percentageUpdate', {
                    percentage: percentage
                });
        },

        /**
         * Update slider state after outer action
         * @param {object} event
         * @param {object} data
         */
        updateSlider: function (event, data) {
            let percentage = data.percentage;

            this.minReached = percentage === 0;
            this.maxReached = percentage === 100;

            this.setControllerPosition(percentage, true, false);
        },

        /**
         * Set controller position according to percentage or angle
         * @param {number} value
         * @param {boolean} isPercentage
         * @param {boolean} updatePercentage
         */
        setControllerPosition: function (value, isPercentage = false, updatePercentage = true) {
            let angle = isPercentage ? (value / 100 * MAX_ANGLE) : value,
                angleRad = angle * Math.PI / 180;

            let dotX = Math.sin(angleRad) * this.radius + this.centerY,
                dotY = -Math.cos(angleRad) * this.radius + this.centerX;

            $(this.element).find('.radial-controller').css({
                'left': (dotX - this.borderWidth / 2) + 'px',
                'top': (dotY - this.borderWidth / 2) + 'px'
            });

            if (updatePercentage) {
                this.updatePercentage(angle);
            }
        },

        /**
         * Get value by controller position
         * @returns {number}
         */
        getValueFromController: function () {
            let $controller = $(this.element).find('.radial-controller'),
                currentX = parseInt($controller.css('left')) + this.borderWidth / 2,
                currentY = parseInt($controller.css('top'))+ this.borderWidth / 2;

            return this.getAngleByCoordinates(currentX, currentY);
        },

        /**
         * Get angle by coordinates
         * @param {number} x
         * @param {number} y
         * @returns {number}
         */
        getAngleByCoordinates: function (x, y) {
            let angle,
                deltaX = x - this.centerX,
                deltaY = y - this.centerY;

            if (deltaX === 0) {
                angle = (deltaY > 0) ? 180 : 0;
            } else {
                angle = Math.atan(deltaY / deltaX) * 180 / Math.PI + (deltaX > 0 ? 90 : 270);
            }

            return angle;
        }
    });
});
