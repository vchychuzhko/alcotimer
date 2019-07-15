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
            $(this.element).on('timer.updateSettings', function () {
                this.loadSettings();
                this.updateTimer();
            }.bind(this));
            $(this.element).on('timer.percentageUpdate', this.options.valueContainer, this.updateTimer.bind(this));
        },

        /**
         * Load and set configurations and settings
         */
        initTimer: function () {
            this.state = STOPPED_STATE;
            this.loadSettings();
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
         */
        loadSettings: function () {
            let settings = JSON.parse(localStorage.getItem('settings'));

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
                this.enteredTime = this.defaultTime * 60;
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
            this.setTime(this.randomTime);
            console.log(this.randomTime);
            console.log('random');
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
                        console.log(this.currentTime);
                    }
                }.bind(this), 1000);

                $(this.element).addClass('in-progress');
                this.state = RUNNING_STATE;
                console.log('start');
            }
        },

        /**
         * Pause the timer
         */
        pause: function () {
            clearInterval(this.timerInterval);
            $(this.element).removeClass('in-progress');
            this.state = STOPPED_STATE;
            console.log('pause');
        },

        /**
         * Stop the timer and reset its time
         */
        stop: function () {
            this.pause();

            this.currentTime = null;
            this.saveConfigurations();
            console.log('stop');
        },

        /**
         * Is triggered when timer countdown is finished
         */
        finish: function () {
            this.setTime(this.currentTime);
            this.stop();

            let audio = new Audio('/pub/media/audio/alert_sound.mp3'),
                playPromise = audio.play();

            if (playPromise !== undefined) {
                playPromise.catch(function () {
                    let $body = $('body'),
                        $blink = $('<div></div>').css({
                            'background-color': '#fff',
                            'height': '100%',
                            'left': '0',
                            'position': 'fixed',
                            'top': '0',
                            'width': '100%',
                            'z-index': '51'
                        });

                    $body.append($blink);

                    setTimeout(function () {
                        $blink.hide(200);
                        $blink.remove();

                        $body.trigger('base.showMessage', {
                            message: 'Time to drink, dude!',
                            duration: 5000,
                        });
                    }.bind(this), 400);
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
            let time = this.secondsToTime(timeInSeconds);

            $('.timer-button-container .timer-button-title').text(time);
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
         */
        updateTimer: function() {
            let $valueContainer = $(this.element).find(this.options.valueContainer),
                timeInSeconds = this.percentageToSeconds(parseFloat($valueContainer.text()));

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
            let $valueContainer = $(this.element).find(this.options.valueContainer);

            $valueContainer.text(this.secondsToPercentage(timeInSeconds));
            $valueContainer.trigger('radial-slider.timeUpdate');
        }
    });
})(jQuery);
