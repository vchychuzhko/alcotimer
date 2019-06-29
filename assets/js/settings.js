;(function ($) {
    $.widget('ava.settings', {
        options: {
            minDefaultValue: 5,
            maxDefaultValue: 20,
            showRandomTime: false,
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
            $(this.element).on('click', '.save-button', function () {
                this.saveSettings();
                window.showMessage('Setting were saved!', 3000);

                setTimeout(function () {
                    $('.menu').trigger('close-menu');
                }, 200);
            }.bind(this));

            $(this.element).on('click', '.reset-button', function () {
                this.resetSettings();
                window.showMessage('Setting were reset to default ones.', 3000);
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
                    $showRandomTimeInput = $(this.element).find('.show-random-time'),
                    $showLoaderInput = $(this.element).find('.show-loader');

                $minTimeInput.val(settings.minTime);
                $minTimeInput.trigger('change');
                $maxTimeInput.val(settings.maxTime);
                $maxTimeInput.trigger('change');
                $showRandomTimeInput.prop('checked' , settings.showRandomTime);
                $showLoaderInput.prop('checked' , settings.showLoader);
            } else {
                this.resetSettings();
            }
        },

        /**
         * Reset settings to default values
         */
        resetSettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $showRandomTimeInputInput = $(this.element).find('.show-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            $minTimeInput.val(this.options.minDefaultValue);
            $minTimeInput.trigger('change');
            $maxTimeInput.val(this.options.maxDefaultValue);
            $maxTimeInput.trigger('change');
            $showRandomTimeInputInput.prop('checked' , this.options.showRandomTime);
            $showLoaderInput.prop('checked' , this.options.showLoader);
        },

        /**
         * Save settings to the local storage
         */
        saveSettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $showRandomTimeInput = $(this.element).find('.show-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader'),
                settings = {
                    'minTime': $minTimeInput.val(),
                    'maxTime': $maxTimeInput.val(),
                    'showRandomTime': $showRandomTimeInput.prop('checked'),
                    'showLoader': $showLoaderInput.prop('checked')
                };

            localStorage.settings = JSON.stringify(settings);
            $('.timer-container').trigger('updateConfigurations');
        },
    });
})(jQuery);
