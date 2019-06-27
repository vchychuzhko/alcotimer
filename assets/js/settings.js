;(function ($) {
    $.widget('ava.settings', {
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
                    $showRandomTime = $(this.element).find('.show-random-time'),
                    $showLoaderInput = $(this.element).find('.show-loader');

                $minTimeInput.val(settings.minTime);
                $minTimeInput.trigger('change');
                $maxTimeInput.val(settings.maxTime);
                $maxTimeInput.trigger('change');
                $showRandomTime.prop('checked' , settings.showRandomTime);
                $showLoaderInput.prop('checked' , settings.showLoader);
            }
        },

        /**
         * Reset settings to default values
         */
        resetSettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $showRandomTime = $(this.element).find('.show-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            $minTimeInput.val(5);
            $minTimeInput.trigger('change');
            $maxTimeInput.val(20);
            $maxTimeInput.trigger('change');
            $showRandomTime.prop('checked' , false);
            $showLoaderInput.prop('checked' , true);
        },

        /**
         * Save settings to the local storage
         */
        saveSettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $showRandomTime = $(this.element).find('.show-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader'),
                settings = {
                    'minTime': $minTimeInput.val(),
                    'maxTime': $maxTimeInput.val(),
                    'showRandomTime': $showRandomTime.prop('checked'),
                    'showLoader': $showLoaderInput.prop('checked')
                };

            localStorage.settings = JSON.stringify(settings);
            $('.timer-container').trigger('updateConfigurations');
        },
    });
})(jQuery);
