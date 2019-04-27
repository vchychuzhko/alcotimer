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
            let $toggleContainer = $(this.element).closest('.menu').find('.toggle-container'),
                $contactUsLink = $(this.element).closest('.menu-list').find('.contact-us .mail-address');

            $(this.element).on('click', '.save-button', function () {
                this.saveSettings();
                window.showMessage('Setting were saved!', 3000);

                setTimeout(function () {
                    $toggleContainer.trigger('click');
                }, 200);
            }.bind(this));

            $(this.element).on('click', '.reset-button', this.resetSettings.bind(this));

            $contactUsLink.on('click', this.copeText.bind(this));
        },

        /**
         * Apply settings from the local storage
         */
        loadSettings: function () {
            let settings = JSON.parse(localStorage.getItem('settings'));

            if (settings !== null) {
                let $minTimeInput = $(this.element).find('.min-value.time'),
                    $minTimeRange = $(this.element).find('.min-range.time'),
                    $maxTimeInput = $(this.element).find('.max-value.time'),
                    $maxTimeRange = $(this.element).find('.max-range.time'),
                    $showRandomTime = $(this.element).find('.show-random-time'),
                    $showLoaderInput = $(this.element).find('.show-loader');

                $minTimeInput.val(settings.minTime);
                $minTimeRange.val(settings.minTime);
                $maxTimeInput.val(settings.maxTime);
                $maxTimeRange.val(settings.maxTime);
                $showRandomTime.prop('checked' , settings.showRandomTime);
                $showLoaderInput.prop('checked' , settings.showLoader);
            }
        },

        /**
         * Reset settings to default values
         */
        resetSettings: function () {
            let $minTimeInput = $(this.element).find('.min-value.time'),
                $minTimeRange = $(this.element).find('.min-range.time'),
                $maxTimeInput = $(this.element).find('.max-value.time'),
                $maxTimeRange = $(this.element).find('.max-range.time'),
                $showRandomTime = $(this.element).find('.show-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            $minTimeInput.val(5);
            $minTimeRange.val(5);
            $maxTimeInput.val(20);
            $maxTimeRange.val(20);
            $showRandomTime.prop('checked' , false);
            $showLoaderInput.prop('checked' , true);

            window.showMessage('Setting were reset to default ones.', 3000);
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
        },

        /**
         * Copy mail address to the clipboard
         */
        copeText: function (event) {
            event.preventDefault();
            let $temp = $("<input>");

            $('body').append($temp);
            $temp.val($(event.target).text()).select();
            document.execCommand('copy');
            $temp.remove();
        }
    });
})(jQuery);
