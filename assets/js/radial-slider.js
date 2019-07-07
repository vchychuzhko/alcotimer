;(function ($) {
    $.widget('ava.radialSlider', {
        options: {
            borderWidth: null,
            centerX: null,
            centerY: null,
            isDragging: false,
            maxAngle: 360,
            maxReached: false,
            minAngle: 0,
            minReached: false,
            offsetLeft: null,
            offsetTop: null,
            previousAngle: null,
            radius: null,
            valueContainer: '.radial-percentage-value'
        },

        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            $(window).trigger('resize');
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(window).on('resize', this.updateCircleParameters.bind(this));

            $(this.element).on('mousedown touchstart', '.radial-slider .radial-controller', function () {
                //Use '.radial-slider' as 2nd parameter to have "teleport" effect on moving out of controller dot
                this.options.isDragging = true;
            }.bind(this));

            $(document).on('mouseup touchend', function () {
                this.options.isDragging = false;
            }.bind(this));

            $(window).on('mousemove touchmove', function (event) {
                if (this.options.isDragging) {
                    try {
                        let touch = event.originalEvent.touches ? event.originalEvent.touches[0] : undefined,
                            targetX = (event.pageX || touch.pageX) - this.options.offsetLeft - this.options.borderWidth / 2,
                            targetY = (event.pageY || touch.pageY) - this.options.offsetTop - this.options.borderWidth / 2,
                            angle = this.getAngleByCoordinates(targetX, targetY),
                            stepIndicatorDifference = 10;

                        if (this.options.minReached) {
                            if (targetX - this.options.centerX < 0) {
                                angle = this.options.minAngle;
                            } else {
                                this.options.minReached = false;
                            }
                        } else if (this.options.maxReached) {
                            if (targetX - this.options.centerX > 0) {
                                angle = this.options.maxAngle;
                            } else {
                                this.options.maxReached = false;
                            }
                        } else if (angle >= this.options.maxAngle - stepIndicatorDifference
                            || angle <= this.options.minAngle + stepIndicatorDifference
                        ) {
                            if (this.options.previousAngle === null) {
                                this.options.previousAngle = angle;
                            } else {
                                if (this.options.previousAngle >= this.options.maxAngle - stepIndicatorDifference
                                    && angle <= this.options.minAngle + stepIndicatorDifference
                                ) {
                                    angle = this.options.maxAngle;
                                    this.options.maxReached = true;
                                } else if (this.options.previousAngle <= this.options.minAngle + stepIndicatorDifference
                                    && angle >= this.options.maxAngle - stepIndicatorDifference
                                ) {
                                    angle = this.options.minAngle;
                                    this.options.minReached = true;
                                }
                            }
                        } else {
                            this.options.previousAngle = null;
                        }

                        this.setControllerPosition(angle);
                    } catch (e) {
                        //do nothing, touch error happened
                    }
                }
            }.bind(this));

            $(this.element).on('timeUpdate', this.options.valueContainer, function (event) {
                let $valueContainer = $(event.target),
                    percentage = parseFloat($valueContainer.text());

                if (percentage === 0) {
                    this.options.minReached = true;
                }

                if (percentage === 100) {
                    this.options.maxReached = true;
                }

                this.setControllerPosition(percentage, true);
            }.bind(this));
        },

        /**
         * Calculate and update radial circle parameters
         */
        updateCircleParameters: function () {
            let $circle = $(this.element).find('.radial-slider');

            this.options.borderWidth = parseFloat($circle.css('border-left-width'));
            this.options.offsetLeft = $circle.offset().left;
            this.options.offsetTop = $circle.offset().top;
            this.options.centerX = ($circle.outerWidth() - this.options.borderWidth) / 2;
            this.options.centerY = ($circle.outerHeight() - this.options.borderWidth) / 2;
            this.options.radius = ($circle.outerWidth() - this.options.borderWidth) / 2;
        },

        /**
         * Update percentage value regarding slider position
         * @param {number} angle
         */
        updatePercentage: function (angle) {
            let percentage = angle / this.options.maxAngle * 100,
                $valueContainer = $(this.element).find(this.options.valueContainer);

            $valueContainer.text(percentage);
            $valueContainer.trigger('percentageUpdate');
        },

        /**
         * Set controller position according to percentage or angle
         * @param {number} value
         * @param {boolean} isPercentage
         */
        setControllerPosition: function (value, isPercentage = false) {
            let angle = isPercentage ? (value / 100 * this.options.maxAngle) : value,
                angleRad = angle * Math.PI / 180;

            let dotX = Math.sin(angleRad) * this.options.radius + this.options.centerY,
                dotY = -Math.cos(angleRad) * this.options.radius + this.options.centerX;

            $(this.element).find('.radial-controller').css({
                'left': (dotX - this.options.borderWidth / 2) + 'px',
                'top': (dotY - this.options.borderWidth / 2) + 'px'
            });

            this.updatePercentage(angle);
        },

        /**
         * Get value by controller position
         * @return {number}
         */
        getValueFromController: function () {
            let $controller = $(this.element).find('.radial-controller'),
                currentX = parseInt($controller.css('left')) + this.options.borderWidth / 2,
                currentY = parseInt($controller.css('top'))+ this.options.borderWidth / 2;

            return this.getAngleByCoordinates(currentX, currentY);
        },

        /**
         * Get angle by coordinates
         * @param {number} x
         * @param {number} y
         * @return {number}
         */
        getAngleByCoordinates: function (x, y) {
            let angle,
                deltaX = x - this.options.centerX,
                deltaY = y - this.options.centerY;

            if (deltaX === 0) {
                angle = (deltaY > 0) ? 180 : 0;
            } else {
                angle = Math.atan(deltaY / deltaX) * 180 / Math.PI + (deltaX > 0 ? 90 : 270);
            }

            return angle;
        }
    });
})(jQuery);
