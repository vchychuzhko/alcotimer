;(function ($) {
    $.widget('awesome.settings', {
        options: {
            hideRandomTime: false,
            defaultMinValue: 5,
            defaultMaxValue: 20,
            showLoader: true
        },

        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            this.loadSettings();
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(this.element).on('click', '.apply-button', function () {
                this.applySettings();
                this.saveSettings();
                $('body').trigger('message.showMessage', {
                    message: 'Settings were applied!'
                });

                setTimeout(function () {
                    $('.menu').trigger('menu.closeMenu');
                }, 200);
            }.bind(this));

            $(this.element).on('click', '.reset-button', function () {
                this.resetSettings();
                $('body').trigger('message.showMessage', {
                    message: 'Settings were reset to default ones.'
                });
            }.bind(this));
        },

        /**
         * Apply settings from the local storage
         */
        loadSettings: function () {
            let settings = JSON.parse(localStorage.getItem('settings'));

            if (settings !== null) {
                let $minTimeInput = $(this.element).find('.min-value.time'),
                    $maxTimeInput = $(this.element).find('.max-value.time'),
                    $hideRandomTimeInput = $(this.element).find('.hide-random-time'),
                    $showLoaderInput = $(this.element).find('.show-loader');

                $minTimeInput.val(settings.minTime);
                $minTimeInput.trigger('change');
                $maxTimeInput.val(settings.maxTime);
                $maxTimeInput.trigger('change');
                $hideRandomTimeInput.prop('checked' , settings.hideRandomTime);
                $showLoaderInput.prop('checked' , settings.showLoader);
            } else {
                this.resetSettings();
            }

            this.applySettings();
        },

        /**
         * Reset settings to default values
         */
        resetSettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $hideRandomTimeInput = $(this.element).find('.hide-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            $minTimeInput.val(this.options.defaultMinValue);
            $minTimeInput.trigger('change');
            $maxTimeInput.val(this.options.defaultMaxValue);
            $maxTimeInput.trigger('change');
            $hideRandomTimeInput.prop('checked' , this.options.hideRandomTime);
            $showLoaderInput.prop('checked' , this.options.showLoader);

            this.saveSettings();
        },

        /**
         * Apply settings by entered values
         */
        applySettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $hideRandomTimeInput = $(this.element).find('.hide-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            this.options.defaultMinValue = parseInt($minTimeInput.val());
            this.options.defaultMaxValue = parseInt($maxTimeInput.val());
            this.options.hideRandomTime = $hideRandomTimeInput.prop('checked');
            this.options.showLoader = $showLoaderInput.prop('checked');

            $('.timer-container').trigger('timer.updateSettings', {
                'settings': {
                    'minTime': this.options.defaultMinValue,
                    'maxTime': this.options.defaultMaxValue,
                    'hideRandomTime': this.options.hideRandomTime,
                    'showLoader': this.options.showLoader
                }
            });
        },

        /**
         * Save settings to the local storage
         */
        saveSettings: function () {
            localStorage.settings = JSON.stringify({
                'minTime': this.options.defaultMinValue,
                'maxTime': this.options.defaultMaxValue,
                'hideRandomTime': this.options.hideRandomTime,
                'showLoader': this.options.showLoader
            });
        }
    });
})(jQuery);
