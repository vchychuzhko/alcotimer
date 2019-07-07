;(function ($) {
    $.widget('ava.timer', {
        options: {
            currentTime: null,
            defaultTime: null,
            enteredTime: null,
            lastTic: null,
            maxTime: null,
            minTime: null,
            randomTime: null,
            state: 'stopped',
            timerInterval: null,
            valueContainer: '.radial-percentage-value'
        },

        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            $(document).on('ready', this.initConfigs.bind(this));
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(this.element).on('click', '.timer-button', this.toggleTimer.bind(this));
            $(this.element).on('click', '.random-button', this.setRandom.bind(this));
            $(this.element).on('updateSettings', this.updateSettings.bind(this));
            $(this.element).on('percentageUpdate', this.options.valueContainer, this.updateTimer.bind(this));
        },

        /**
         * Load and set configurations and settings
         */
        initConfigs: function () {
            this.updateSettings();
            this.loadConfigurations();
            this.setTime(this.options.currentTime);
            this.reset();
        },

        /**
         * Retrieve and update settings from the menu
         */
        updateSettings: function () {
            this.options.minTime = parseInt($('.settings .min-value.time').val()) * 60;
            this.options.maxTime = parseInt($('.settings .max-value.time').val()) * 60;
        },

        /**
         * Load configurations from the local storage
         */
        loadConfigurations: function () {
            let timerConfig = JSON.parse(localStorage.getItem('timerConfigurations'));

            if (timerConfig !== null) {
                this.options.state = timerConfig.state;
                this.options.currentTime = timerConfig.currentTime;
                this.options.lastTic = timerConfig.lastTic;
            } else {
                this.options.currentTime = this.options.enteredTime = this.options.defaultTime * 60;
                this.saveConfigurations();
            }
        },

        /**
         * Save configurations to the local storage
         */
        saveConfigurations: function () {
            let timerConfig = {
                'state': this.options.state,
                'currentTime': this.options.currentTime,
                'lastTic': this.options.lastTic,
            };

            localStorage.timerConfigurations = JSON.stringify(timerConfig);
        },

        /**
         * Start/stop the timer
         */
        toggleTimer: function () {
            if ($(this.element).is('.in-progress')) {
                this.stop();
            } else {
                this.start();
            }
        },

        /**
         * Load and update configurations
         */
        updateConfigs: function () {
            this.options.minTime = parseInt($('.settings .min-value.time').val()) * 60;
            this.options.maxTime = parseInt($('.settings .max-value.time').val()) * 60;
            this.reset();
        },

        /**
         * Stop current timer and set random time
         */
        setRandom: function () {
            this.stop();
            this.options.randomTime = Math.floor(
                Math.random() * (this.options.maxTime - this.options.minTime + 1) + this.options.minTime
            );
            this.showTime(this.options.randomTime);
            console.log(this.options.randomTime);
            console.log('random');
        },

        /**
         * Start/resume the timer
         */
        start: function () {
            if (!this.options.randomTime) {
                let newTime = this.angleToSeconds(
                    parseInt($('.timer-button-container .time-value').html())
                );

                if (newTime !== this.options.enteredTime) {
                    this.options.currentTime = this.options.enteredTime = newTime;
                }
            } else {
                this.options.currentTime = this.options.enteredTime = this.options.randomTime;
                this.options.randomTime = null;
            }

            this.options.timerInterval = setInterval(function () {
                if (--this.options.currentTime === 0) {
                    this.finish();
                }

                this.setTime(this.options.currentTime);
                console.log(this.options.currentTime);
            }.bind(this), 1000);
            $(this.element).addClass('in-progress');
            console.log('start');
        },

        /**
         * Stop/pause the timer
         */
        stop: function () {
            if ($(this.element).is('.in-progress')) {
                clearInterval(this.options.timerInterval);
                $(this.element).removeClass('in-progress');
            }
            console.log('stop');
        },

        /**
         * Stop the timer and reset current time to the last entered
         */
        reset: function () {
            this.stop();
            this.options.currentTime = this.options.enteredTime;
            console.log('reset');
        },

        /**
         * Is triggered when timer is finished
         */
        finish: function () {
            this.stop();
            var audioElement = document.createElement('audio'); //@TODO: try 'let' keyword
            audioElement.setAttribute('src', '/pub/media/audio/alert_sound.mp3');
            audioElement.play();
            console.log('finish');
        },

        /**
         * Convert percent to seconds
         * @param {number} percent
         * @returns {number}
         */
        percentageToSeconds: function(percent) {
            return Math.round(percent / 100 * (this.options.maxTime - this.options.minTime)) + this.options.minTime;
        },

        /**
         * Convert seconds to percent
         * @param {number} seconds
         * @returns {number}
         */
        secondsToPercentage: function(seconds) {
            if (seconds > this.options.maxTime) {
                seconds = this.options.maxTime;
            }

            if (seconds < this.options.minTime) {
                seconds = this.options.minTime;
            }

            return (seconds - this.options.minTime) / (this.options.maxTime - this.options.minTime) * 100;
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
            let time = this.secondsToTime(timeInSeconds);

            $('.timer-button-container .timer-button-title').text(time);
            //@TODO: temporary place for displaying the time

            this.saveConfigurations();

            if (updateSlider) {
                this.updateSlider(timeInSeconds);
            }
        },

        /**
         * Update timer according to slider value
         * @param {Event} event
         */
        updateTimer: function(event) {
            let $valueContainer = $(event.target),
                timeInSeconds = this.percentageToSeconds(parseFloat($valueContainer.text()));

            this.options.currentTime = this.options.enteredTime = timeInSeconds;

            this.setTime(timeInSeconds, false);
        },

        /**
         * Update radial slider position
         * @param {number} timeInSeconds
         */
        updateSlider: function(timeInSeconds) {
            let $valueContainer = $(this.element).find(this.options.valueContainer);

            $valueContainer.text(this.secondsToPercentage(timeInSeconds));
            $valueContainer.trigger('timeUpdate');
        }
    });
})(jQuery);
