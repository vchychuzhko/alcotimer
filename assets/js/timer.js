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
            timerInterval: null
        },

        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            this.initConfigs();
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(this.element).on('click', '.timer-button', this.toggleTimer.bind(this));
            $(this.element).on('click', '.random-button', this.setRandom.bind(this));
            $(this.element).on('updateSettings', this.updateSettings.bind(this));
        },

        /**
         * Load and set configurations and settings
         */
        initConfigs: function () {
            this.updateSettings();
            this.loadConfigurations();
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
                this.showTime(this.options.currentTime);
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
         *
         * @param {int} angle
         * @returns {int}
         */
        angleToSeconds: function(angle) {
            return Math.round((angle / 360) * (this.options.maxTime - this.options.minTime)) + this.options.minTime;
        },

        /**
         *
         * @param {int} time
         */
        showTime: function(time) {
            let minutes = Math.trunc(time / 60),
                seconds = Math.round((time / 60 - minutes) * 60);

            $('.timer-button-container .timer-button-title').html(minutes+ ':' + (seconds < 10 ? '0' : '') + seconds);
        }
    });
})(jQuery);
