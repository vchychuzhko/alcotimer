define([
    'jquery',
    'messenger',
    'translator',
    'jquery/ui',
    'howler',
], function ($, messenger, __) {
    'use strict'

    const RUNNING_STATE = 'running',
          STOPPED_STATE = 'stopped';

    $.widget('awesome.timer', {
        options: {
            defaultTime: 9,
            radialContainerSelector: '.radial-container',
            sound: '',
        },

        /**
         * Constructor
         */
        _create: function () {
            this.initBindings();
            $(document).ready(this.initTimer.bind(this));
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(this.element).on('click', '.timer-button', this.toggleTimer.bind(this));
            $(this.element).on('click', '.random-button', this.setRandom.bind(this));

            $(this.element).on('timer.updateSettings', function (event, data) {
                this.pause();
                this.applySettings(data.settings);
                this.setTime(this.enteredTime);
            }.bind(this));

            $(this.element).on(
                'radial-slider.percentageUpdate',
                this.options.radialContainerSelector,
                this.updateTimer.bind(this)
            );
        },

        /**
         * Load and set configurations and settings
         */
        initTimer: function () {
            this.state = STOPPED_STATE;

            this.applySettings();
            this.loadTimerStateData();

            if (this.state === RUNNING_STATE) {
                this.currentTime -= Math.round(($.now() - this.lastTic) / 1000);
                this.setTime(this.currentTime);
                this.start();
            } else {
                this.lastTic = null;

                if (this.currentTime) {
                    this.setTime(this.currentTime);
                } else {
                    this.setTime(this.enteredTime);
                }
            }
        },

        /**
         * Retrieve and update settings from the menu
         * @param {object} settings
         */
        applySettings: function (settings = {}) {
            if ($.isEmptyObject(settings)) {
                settings = JSON.parse(localStorage.getItem('settings'));
            }

            this.minTime = settings.minTime * 60;
            this.maxTime = settings.maxTime * 60;
            this.hideRandomTime = settings.hideRandomTime;
            this.showLoader = settings.showLoader;

            if (!this.showLoader) {
                $(this.element).addClass('no-loader');
            } else {
                $(this.element).removeClass('no-loader');
            }
        },

        /**
         * Load timer data from the local storage
         */
        loadTimerStateData: function () {
            let timerConfig = JSON.parse(localStorage.getItem('timerConfigurations'));

            if (timerConfig !== null) {
                this.state = timerConfig.state;
                this.currentTime = timerConfig.currentTime;
                this.enteredTime = timerConfig.enteredTime;
                this.lastTic = timerConfig.lastTic;
                this.randomTime = timerConfig.randomTime;
            } else {
                this.enteredTime = this.options.defaultTime * 60;
                this.saveConfigurations();
            }
        },

        /**
         * Save configurations to the local storage
         */
        saveConfigurations: function () {
            let timerConfig = {
                'state': this.state,
                'currentTime': this.currentTime,
                'enteredTime': this.enteredTime,
                'lastTic': this.lastTic,
                'randomTime': this.randomTime
            };

            localStorage.timerConfigurations = JSON.stringify(timerConfig);
        },

        /**
         * Start/stop the timer
         */
        toggleTimer: function () {
            if ($(this.element).is('.in-progress')) {
                this.pause();
            } else {
                this.start();
            }

            this.saveConfigurations()
        },

        /**
         * Stop current timer and set random time
         */
        setRandom: function () {
            this.stop();
            this.randomTime = this.percentageToSeconds(Math.random() * 100);
            this.setTime(this.randomTime, true);
        },

        /**
         * Start/resume the timer
         */
        start: function () {
            if (!this.currentTime) {
                this.currentTime = this.randomTime ? this.randomTime : this.enteredTime;
            }

            if (this.currentTime < 0) {
                this.finish();
            } else {
                this.timerInterval = setInterval(function () {
                    if (--this.currentTime <= 0) {
                        this.finish();
                    } else {
                        this.setTime(this.currentTime);
                    }
                }.bind(this), 1000);

                $(this.element).addClass('in-progress');
                this.state = RUNNING_STATE;
            }
        },

        /**
         * Pause the timer
         */
        pause: function () {
            clearInterval(this.timerInterval);
            $(this.element).removeClass('in-progress');
            this.state = STOPPED_STATE;
        },

        /**
         * Stop the timer and reset its time
         * @param {boolean} save
         */
        stop: function (save = true) {
            this.pause();

            this.currentTime = null;

            if (save) {
                this.saveConfigurations();
            }
        },

        /**
         * Is triggered when timer countdown is finished
         */
        finish: function () {
            this.setTime(this.currentTime);
            this.stop();

            if (this.options.sound) {
                let sound = new Howl({
                    src: [this.options.sound],
                });

                sound.play();
            } else {
                messenger.info(__('It time to start!'));
            }
        },

        /**
         * Convert percent to seconds
         * @param {number} percent
         * @returns {number}
         */
        percentageToSeconds: function(percent) {
            return Math.round(percent / 100 * (this.maxTime - this.minTime)) + this.minTime;
        },

        /**
         * Convert seconds to percent
         * @param {number} seconds
         * @returns {number}
         */
        secondsToPercentage: function(seconds) {
            if (seconds > this.maxTime) {
                seconds = this.maxTime;
            }

            if (seconds < this.minTime) {
                seconds = this.minTime;
            }

            return (seconds - this.minTime) / (this.maxTime - this.minTime) * 100;
        },

        /**
         * Convert seconds into a readable time string
         * @param {number} timeInSeconds
         * @returns {string}
         */
        secondsToTime: function(timeInSeconds) {
            let minutes = Math.trunc(timeInSeconds / 60),
                seconds = Math.round((timeInSeconds / 60 - minutes) * 60);

            return minutes+ ':' + (seconds < 10 ? '0' : '') + seconds;
        },

        /**
         * Update time value for the timer
         * @param {number} timeInSeconds
         * @param {boolean} updateSlider
         */
        setTime: function(timeInSeconds, updateSlider = true) {
            let time = this.secondsToTime(timeInSeconds),
                $timeContainer = $('.timer-button-container .timer-time');

            $timeContainer.text(time).toggle(!(this.hideRandomTime && this.randomTime));
            //@TODO: temporary place for displaying the time

            if (this.state === RUNNING_STATE) {
                this.lastTic = $.now();
            }

            this.saveConfigurations();

            if (updateSlider && (this.currentTime ? (this.currentTime >= this.minTime) : true)) {
                this.updateSlider(timeInSeconds);
            }
        },

        /**
         * Update timer according to slider value
         * @param {object} event
         * @param {object} data
         * @property {number} data.percentage
         */
        updateTimer: function(event, data) {
            this.stop(false);
            let timeInSeconds = this.percentageToSeconds(data.percentage);

            this.enteredTime = timeInSeconds;
            this.currentTime = null;
            this.randomTime = null;
            this.saveConfigurations();

            this.setTime(timeInSeconds, false);
        },

        /**
         * Update radial slider position
         * @param {number} timeInSeconds
         */
        updateSlider: function(timeInSeconds) {
            let $slider = $(this.element).find(this.options.radialContainerSelector);

            $slider.trigger('radial-slider.percentageSet', {
                percentage: this.secondsToPercentage(timeInSeconds)
            });
        }
    });
});
