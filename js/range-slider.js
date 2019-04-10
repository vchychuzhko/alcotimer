;(function ($) {
    $.widget('ava.rangeSlider', {
        options: {
            difference: 2
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
            let $minRange = $(this.element).find('.min-range'),
                $maxRange = $(this.element).find('.max-range'),
                $minInput = $(this.element).find('.min-value'),
                $maxInput = $(this.element).find('.max-value');

            $minRange.on('input', function () {
                let minValue = $(event.target).val(),
                    maxValue = $maxRange.val();

                if (minValue > maxValue - this.options.difference) {
                    $maxRange.val(parseInt(minValue) + this.options.difference);

                    if (maxValue === $maxRange.attr('max')) {
                        $minRange.val($maxRange.attr('max') - this.options.difference);
                    }
                }
                $minInput.val($minRange.val());
                $maxInput.val($maxRange.val());
            }.bind(this));

            $maxRange.on('input', function () {
                let maxValue = $(event.target).val(),
                    minValue = $minRange.val();

                if (maxValue < parseInt(minValue) + this.options.difference) {
                    $minRange.val(maxValue - this.options.difference);

                    if (minValue === $minRange.attr('min')) {
                        $maxRange.val(this.options.difference);
                    }
                }
                $minInput.val($minRange.val());
                $maxInput.val($maxRange.val());
            }.bind(this));

            $minInput.on('change', function () {
                $minRange.val($(event.target).val());
            });

            $maxInput.on('change', function () {
                $maxRange.val($(event.target).val());
            });
        }
    });
})(jQuery);
