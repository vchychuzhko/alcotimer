;(function ($) {
    $.widget('ava.timer', {
        options: {
            minTime: 300,
            maxTime: 1200,
            enteredTime: null,
            currentTime: null,
            randomTime: null,
            interval: null
        },

        /**
         * Constructor
         * @private
         */
        _create: function () {
            this.initBindings();
            this.updateConfigs();
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            $(this.element).on('click', '.timer-button', this.toggleTimer.bind(this));
            $(this.element).on('click', '.random-button', this.setRandom.bind(this));
            $(this.element).on('updateConfigurations', this.updateConfigs.bind(this));
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
         *
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

            this.options.interval = setInterval(function () {
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
         * Stop timer
         */
        stop: function () {
            if ($(this.element).is('.in-progress')) {
                clearInterval(this.options.interval);
                $(this.element).removeClass('in-progress');
            }
            console.log('stop');
        },

        /**
         *
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
            var audioElement = document.createElement('audio');
            audioElement.setAttribute('src', '/pub/media/audio/alert_sound.mp3');
            audioElement.play();
            console.log('finish');
        },

        /**
         *
         * @param {int} input
         * @returns {int}
         */
        angleToSeconds: function(input) {
            return Math.round((input / 360) * (this.options.maxTime - this.options.minTime)) + this.options.minTime;
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
