;(function ($) {
    $.widget('awesome.settings', {
        options: {
            hideRandomTime: false,
            minDefaultValue: 5,
            maxDefaultValue: 20,
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
                $('body').trigger('base.showMessage', {
                    message: 'Settings were applied!'
                });

                setTimeout(function () {
                    $('.menu').trigger('close-menu');
                }, 200);
            }.bind(this));

            $(this.element).on('click', '.reset-button', function () {
                this.resetSettings();
                $('body').trigger('base.showMessage', {
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

            this.applySettings(false);
        },

        /**
         * Reset settings to default values
         */
        resetSettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $hideRandomTimeInput = $(this.element).find('.hide-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            $minTimeInput.val(this.options.minDefaultValue);
            $minTimeInput.trigger('change');
            $maxTimeInput.val(this.options.maxDefaultValue);
            $maxTimeInput.trigger('change');
            $hideRandomTimeInput.prop('checked' , this.options.hideRandomTime);
            $showLoaderInput.prop('checked' , this.options.showLoader);

            this.saveSettings();
        },

        /**
         * Apply settings by entered values
         * @param {boolean} save
         */
        applySettings: function (save = true) {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $hideRandomTimeInput = $(this.element).find('.hide-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            this.options.minDefaultValue = parseInt($minTimeInput.val());
            this.options.maxDefaultValue = parseInt($maxTimeInput.val());
            this.options.hideRandomTime = $hideRandomTimeInput.prop('checked');
            this.options.showLoader = $showLoaderInput.prop('checked');

            if (save) {
                this.saveSettings();
            }

            $('.timer-container').trigger('timer.updateSettings');
        },

        /**
         * Save settings to the local storage
         */
        saveSettings: function () {
            localStorage.settings = JSON.stringify({
                'minTime': this.options.minDefaultValue,
                'maxTime': this.options.maxDefaultValue,
                'hideRandomTime': this.options.hideRandomTime,
                'showLoader': this.options.showLoader
            });
        }
    });
})(jQuery);
