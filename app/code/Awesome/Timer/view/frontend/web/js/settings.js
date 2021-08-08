define([
    'jquery',
    'notification',
    'translator',
    'jquery/ui',
], function ($, notification, __) {
    'use strict'

    $.widget('awesome.settings', {
        options: {
            hideRandomTime: false,
            maxTime: 20,
            minTime: 5,
            showLoader: true
        },

        /**
         * Constructor
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
                notification.info(__('Settings were applied'));

                setTimeout(function () {
                    $(document).trigger('menu.close');
                }, 200);
            }.bind(this));

            $(this.element).on('click', '.reset-button', this.resetSettings.bind(this));
        },

        /**
         * Apply settings from the local storage
         */
        loadSettings: function () {
            let settings = JSON.parse(localStorage.getItem('settings'));

            if (settings !== null) {
                let $minTimeInput = $(this.element).find('.random-time .value.min'),
                    $maxTimeInput = $(this.element).find('.random-time .value.max'),
                    $hideRandomTimeInput = $(this.element).find('.hide-random-time'),
                    $showLoaderInput = $(this.element).find('.show-loader');

                $minTimeInput.val(settings.minTime).trigger('change');
                $maxTimeInput.val(settings.maxTime).trigger('change');
                $hideRandomTimeInput.prop('checked', settings.hideRandomTime);
                $showLoaderInput.prop('checked', settings.showLoader);
            } else {
                this.resetSettings();
            }

            this.applySettings();
        },

        /**
         * Reset settings to default values
         */
        resetSettings: function () {
            let $minTimeInput = $(this.element).find('.random-time .value.min'),
                $maxTimeInput = $(this.element).find('.random-time .value.max'),
                $hideRandomTimeInput = $(this.element).find('.hide-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            this.minTime = this.options.minTime;
            this.maxTime = this.options.maxTime;
            this.hideRandomTime = this.options.hideRandomTime;
            this.showLoader = this.options.showLoader;

            $minTimeInput.val(this.minTime).trigger('change');
            $maxTimeInput.val(this.maxTime).trigger('change');
            $hideRandomTimeInput.prop('checked', this.hideRandomTime);
            $showLoaderInput.prop('checked', this.showLoader);

            this.saveSettings();
        },

        /**
         * Apply settings by entered values
         */
        applySettings: function () {
            let $minTimeInput = $(this.element).find('.random-time .value.min'),
                $maxTimeInput = $(this.element).find('.random-time .value.max'),
                $hideRandomTimeInput = $(this.element).find('.hide-random-time'),
                $showLoaderInput = $(this.element).find('.show-loader');

            this.minTime = parseInt($minTimeInput.val());
            this.maxTime = parseInt($maxTimeInput.val());
            this.hideRandomTime = $hideRandomTimeInput.prop('checked');
            this.showLoader = $showLoaderInput.prop('checked');

            $('.timer-container').trigger('timer.updateSettings', {
                'settings': {
                    'minTime': this.minTime,
                    'maxTime': this.maxTime,
                    'hideRandomTime': this.hideRandomTime,
                    'showLoader': this.showLoader
                }
            });
        },

        /**
         * Save settings to the local storage
         */
        saveSettings: function () {
            localStorage.settings = JSON.stringify({
                'minTime': this.minTime,
                'maxTime': this.maxTime,
                'hideRandomTime': this.hideRandomTime,
                'showLoader': this.showLoader
            });
        }
    });
});
