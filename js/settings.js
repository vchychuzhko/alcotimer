;(function ($) {
    $.widget('ava.settings', {
        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            this.applySettings(false);
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            let $saveButton = $(this.element).find('.save-button'),
                $resetButton = $(this.element).find('.reset-button'),
                $toggleContainer = $(this.element).closest('.menu').find('.toggle-container');

            $saveButton.on('click', function () {
                this.saveSettings();
                this.showMessage('Saved!', 1000);
                setTimeout(function () {
                    $toggleContainer.trigger('click');
                }, 1000);
            }.bind(this));

            $resetButton.on('click', this.applySettings.bind(this));
        },

        /**
         * Apply settings from the local storage
         * @param showError
         */
        applySettings: function (showError = true) {
            let settings = JSON.parse(localStorage.getItem('settings')),
                $minTimeInput = $(this.element).find('.min-value.time'),
                $minTimeRange = $(this.element).find('.min-range.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $maxTimeRange = $(this.element).find('.max-range.time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            if (settings !== null) {
                $minTimeInput.val(settings.minTime);
                $minTimeRange.val(settings.minTime);
                $maxTimeInput.val(settings.maxTime);
                $maxTimeRange.val(settings.maxTime);
                $showLoaderInput.prop('checked' , settings.showLoader);
            } else if (showError) {
                this.showMessage('Settings were not found. Please, save new configurations.', 5000, true);
            }
        },

        /**
         * Save settings to the local storage
         */
        saveSettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $showLoaderInput = $(this.element).find('.show-loader'),
                settings = {
                    'minTime': $minTimeInput.val(),
                    'maxTime': $maxTimeInput.val(),
                    'showLoader': $showLoaderInput.prop('checked')
                };

            localStorage.settings = JSON.stringify(settings);
        },

        /**
         * Show message in the bottom of settings
         * @param message
         * @param duration
         * @param isError
         */
        showMessage: function (message, duration = 5000, isError = false) {
            let styles = 'font-size: 20px; margin-top: 20px;' + (isError ? ' color: #f00;' : ''),
                $message = $('<p style="' + styles + '">' + message + '</p>');

            $(this.element).append($message);

            setTimeout(function () {
                $message.remove();
            }.bind(this), duration);
        }
    });
})(jQuery);
