;(function ($) {
    let RUNNING_STATE = 'running',
        STOPPED_STATE = 'stopped';

    $.widget('awesome.timer', {
        options: {
            valueContainer: '.radial-percentage-value'
        },

        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            $(document).on('ready', this.initTimer.bind(this));
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(this.element).on('click', '.timer-button', this.toggleTimer.bind(this));
            $(this.element).on('click', '.random-button', this.setRandom.bind(this));
            $(this.element).on('updateSettings', function () {
                this.updateSettings();
                this.updateTimer();
            }.bind(this));
            $(this.element).on('percentageUpdate', this.options.valueContainer, this.updateTimer.bind(this));
        },

        /**
         * Load and set configurations and settings
         */
        initTimer: function () {
            this.options.state = STOPPED_STATE;
            this.updateSettings();
            this.loadConfigurations();

            if (this.options.state === RUNNING_STATE) {
                this.options.currentTime -= Math.round(($.now() - this.options.lastTic) / 1000);
                this.setTime(this.options.currentTime);
                this.start();
            } else {
                this.options.lastTic = null;

                if (this.options.currentTime) {
                    this.setTime(this.options.currentTime);
                } else {
                    this.setTime(this.options.enteredTime);
                }
            }
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
                this.options.enteredTime = timerConfig.enteredTime;
                this.options.lastTic = timerConfig.lastTic;
                this.options.randomTime = timerConfig.randomTime;
            } else {
                this.options.enteredTime = this.options.defaultTime * 60;
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
                'enteredTime': this.options.enteredTime,
                'lastTic': this.options.lastTic,
                'randomTime': this.options.randomTime
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
            this.options.randomTime = this.percentageToSeconds(Math.random() * 100);
            this.setTime(this.options.randomTime);
            console.log(this.options.randomTime);
            console.log('random');
        },

        /**
         * Start/resume the timer
         */
        start: function () {
            if (!this.options.currentTime) {
                this.options.currentTime = this.options.randomTime ? this.options.randomTime : this.options.enteredTime;
            }

            if (this.options.currentTime < 0) {
                this.finish();
            } else {
                this.options.timerInterval = setInterval(function () {
                    if (--this.options.currentTime <= 0) {
                        this.finish();
                    } else {
                        this.setTime(this.options.currentTime);
                        console.log(this.options.currentTime);
                    }
                }.bind(this), 1000);

                $(this.element).addClass('in-progress');
                this.options.state = RUNNING_STATE;
                console.log('start');
            }
        },

        /**
         * Pause the timer
         */
        pause: function () {
            clearInterval(this.options.timerInterval);
            $(this.element).removeClass('in-progress');
            this.options.state = STOPPED_STATE;
            console.log('pause');
        },

        /**
         * Stop the timer and reset its time
         */
        stop: function () {
            this.pause();

            this.options.currentTime = null;
            this.saveConfigurations();
            console.log('stop');
        },

        /**
         * Is triggered when timer countdown is finished
         */
        finish: function () {
            this.setTime(this.options.currentTime);
            this.stop();

            let audio = new Audio('/pub/media/audio/alert_sound.mp3'),
                playPromise = audio.play();

            if (playPromise !== undefined) {
                playPromise.catch(function () {
                    $('body').trigger('base.showMessage', {
                        message: 'Time to drink, dude!',
                    });
                    //@TODO: Play is forbidden by the browser, should be handled in some way. No workaround has an effect.
                });
            }
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

            if (this.options.state === RUNNING_STATE) {
                this.options.lastTic = $.now();
            }

            this.saveConfigurations();

            if (updateSlider && (this.options.currentTime ? (this.options.currentTime >= this.options.minTime) : true)) {
                this.updateSlider(timeInSeconds);
            }
        },

        /**
         * Update timer according to slider value
         */
        updateTimer: function() {
            let $valueContainer = $(this.element).find(this.options.valueContainer),
                timeInSeconds = this.percentageToSeconds(parseFloat($valueContainer.text()));

            this.options.enteredTime = timeInSeconds;
            this.options.currentTime = null;
            this.options.randomTime = null;
            this.saveConfigurations();

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
