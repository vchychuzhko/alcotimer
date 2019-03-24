;(function ($) {
    $.widget('vlad.settings', {
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
                $toggleContainer.trigger('click');
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
                $maxTimeRange = $(this.element).find('.max-range.time');

            if (settings !== null) {
                $minTimeInput.val(settings.minTime);
                $minTimeRange.val(settings.minTime);
                $maxTimeInput.val(settings.maxTime);
                $maxTimeRange.val(settings.maxTime);
            } else if (showError) {
                this.showMessage('Settings were not found. Please, save new configurations.', true);
            }
        },

        /**
         * Save settings to the local storage
         */
        saveSettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                settings = {
                'minTime': $minTimeInput.val(),
                'maxTime': $maxTimeInput.val()
            };

            localStorage.settings = JSON.stringify(settings);
        },

        /**
         * Show message in the bottom of settings
         * @param message
         * @param isError
         */
        showMessage: function (message, isError = false) {
            let styles = 'font-size: 20px; margin-top: 20px;' + (isError ? ' color: #f00;' : ''),
                $message = $('<p style="' + styles + '">' + message + '</p>');

            $(this.element).append($message);

            setTimeout(function () {
                $message.remove();
            }.bind(this), 5000);
        }
    });
})(jQuery);
