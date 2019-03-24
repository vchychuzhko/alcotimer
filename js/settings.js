;(function ($) {
    $.widget('vlad.settings', {
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
            let $saveButton = $(this.element).find('.save-button'),
                $resetButton = $(this.element).find('.reset-button'),
                $minTimeInput = $(this.element).find('.min-value.time'),
                $minTimeRange = $(this.element).find('.min-range.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $maxTimeRange = $(this.element).find('.max-range.time'),
                $toggleContainer = $(this.element).find('.toggle-container');

            $saveButton.on('click', function () {
                let settings = {
                    'minTime': $minTimeInput.val(),
                    'maxTime': $maxTimeInput.val()
                };

                localStorage.settings = JSON.stringify(settings);
                $toggleContainer.trigger('click');
            }.bind(this));

            $resetButton.on('click', function () {
                let settings = JSON.parse(localStorage.getItem('settings'));

                if (settings !== null) {
                    $minTimeInput.val(settings.minTime);
                    $minTimeRange.val(settings.minTime);
                    $maxTimeInput.val(settings.maxTime);
                    $maxTimeRange.val(settings.maxTime);
                } else {
                    this.showMessage('Settings were not found. Please, save new configurations.', true);
                }
            }.bind(this));
        },

        /**
         *
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
