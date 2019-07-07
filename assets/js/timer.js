;(function ($) {
    let RUNNING_STATE = 'running',
        STOPPED_STATE = 'stopped';

    $.widget('ava.timer', {
        options: {
            currentTime: null,
            defaultTime: null,
            enteredTime: null,
            lastTic: null,
            maxTime: null,
            minTime: null,
            randomTime: null,
            state: STOPPED_STATE,
            timerInterval: null,
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
            // $(this.element).on('click', '.random-button', this.setRandom.bind(this));
            $(this.element).on('updateSettings', this.updateSettings.bind(this));
            $(this.element).on('percentageUpdate', this.options.valueContainer, this.updateTimer.bind(this));
        },

        /**
         * Load and set configurations and settings
         */
        initTimer: function () {
            this.updateSettings();
            this.loadConfigurations();

            if (this.options.state === RUNNING_STATE) {
                this.options.currentTime -= Math.round(($.now() - this.options.lastTic) / 1000);
                this.setTime(this.options.currentTime);
                this.start();
            } else {
                this.options.lastTic = null;
                this.setTime(this.options.enteredTime);
            }
            // @TODO: when stopped time is lower than min, on page reload min value is used
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

        // /**
        //  * Stop current timer and set random time
        //  */
        // setRandom: function () {
        //     this.pause();
        //     this.options.randomTime = Math.floor(
        //         Math.random() * (this.options.maxTime - this.options.minTime + 1) + this.options.minTime
        //     );
        //     this.showTime(this.options.randomTime);
        //     console.log(this.options.randomTime);
        //     console.log('random');
        // },

        /**
         * Start/resume the timer
         */
        start: function () {
            // if (!this.options.randomTime) {
            //     let newTime = this.angleToSeconds(
            //         parseInt($('.timer-button-container .time-value').text())
            //     );
            //
            //     if (newTime !== this.options.enteredTime) {
            //         this.options.currentTime = this.options.enteredTime = newTime;
            //     }
            // } else {
            if (!this.options.currentTime) {
                this.options.currentTime = this.options.enteredTime;
            }
            //     this.options.randomTime = null;
            // }

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
         * Stop/pause the timer
         * @param {boolean} stop
         */
        pause: function (stop = false) {
            if ($(this.element).is('.in-progress')) {
                clearInterval(this.options.timerInterval);
                $(this.element).removeClass('in-progress');
            }

            if (stop) {
                this.options.currentTime = null;
                this.saveConfigurations();
            }
            this.options.state = STOPPED_STATE;
            console.log('stop');
        },

        /**
         * Is triggered when timer is finished
         */
        finish: function () {
            this.setTime(this.options.currentTime);
            this.pause(true);
            let audioElement = document.createElement('audio'); //@TODO: try 'let' keyword. Seems, it works
            audioElement.setAttribute('src', '/pub/media/audio/alert_sound.mp3');
            let playPromise = audioElement.play();

            if (playPromise !== undefined) {
                playPromise.catch(function (error) {
                    //@TODO: Investigate and handle promise error: play() failed because the user didn't interact with the document first.
                    // Automatic playback failed.
                    // Show a UI element to let the user manually start playback.
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
         * @param {Event} event
         */
        updateTimer: function(event) {
            let $valueContainer = $(event.target),
                timeInSeconds = this.percentageToSeconds(parseFloat($valueContainer.text()));

            this.options.enteredTime = timeInSeconds;
            this.options.currentTime = null;
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
