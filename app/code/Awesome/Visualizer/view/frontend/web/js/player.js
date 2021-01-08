define([
    'Awesome_Visualizer/js/visualizer',
    'jquery',
    'jquery/ui',
], function (visualizer) {
    'use strict'

    const RUNNING_STATE = 'running',
          PAUSED_STATE  = 'paused',
          STOPPED_STATE = 'stopped';

    $.widget('awesome.player', {
        options: {
            canvasSelector: '.canvas',
            playerSelector: '.player',
            playlistConfig: {},
            timeSelector: '.timecode',
            trackNameSelector: '.trackname',
        },

        $canvas: null,
        $player: null,
        $time: null,
        $trackName: null,

        filename: null,
        state: null,

        /**
         * Constructor.
         */
        _create: function () {
            this.initFields();
            this.checkTouchScreen();
            this.initBindings();
            this.calculateCanvasSize();
        },

        /**
         * Init widget fields.
         */
        initFields: function () {
            this.$audio = this.element.get(0).querySelector(this.options.playerSelector);
            this.$canvas = this.element.get(0).querySelector(this.options.canvasSelector);
            this.$time = this.element.get(0).querySelector(this.options.timeSelector);
            this.$trackName = this.element.get(0).querySelector(this.options.trackNameSelector);
        },

        /**
         * Check if screen is touchable and apply responsible changes.
         */
        checkTouchScreen: function () {
            if ('ontouchstart' in document.documentElement) {
                this.$audio.classList.add('nohide');
            }
        },

        /**
         * Init event listeners.
         */
        initBindings: function () {
            window.addEventListener('resize', this.calculateCanvasSize.bind(this));

            document.addEventListener('dragover', function (event) {
                event.preventDefault();
                event.stopPropagation();
            });

            document.addEventListener('drop', function (event) {
                event.preventDefault();
                event.stopPropagation();

                let file = event.dataTransfer.files[0];

                this.fileName = file.name.replace(/\.[^/.]+$/, '');

                this.$audio.setAttribute('src', URL.createObjectURL(file));

                this.$audio.play();
            }.bind(this));
            // @TODO: Check lock screen play

            this.$audio.addEventListener('timeupdate', function (event) {
                let currentTime = event.currentTarget.currentTime;

                this.updateTrackName(this.fileName, Math.floor(currentTime));
                this.updateTime(currentTime);
            }.bind(this));

            this.$audio.addEventListener('play', this.play.bind(this));

            this.$audio.addEventListener('pause', this.pause.bind(this));

            document.addEventListener('keyup', function (event) {
                if (!event.target.matches(this.options.playerSelector)) {
                    this.handlePlayerControls(event);
                }
            }.bind(this));
        },

        /**
         * Update audio track name.
         * @param {string} trackName
         * @param {number|null} timeCode
         */
        updateTrackName: function (trackName, timeCode = null) {
            if (timeCode !== null && this.options.playlistConfig[trackName]) {
                let track = Object.keys(this.options.playlistConfig[trackName])
                    .sort((a, b) => b - a)
                    .find(seconds => seconds <= timeCode)

                trackName = this.options.playlistConfig[trackName][track];
            }

            if (trackName && this.$trackName.innerText !== trackName) {
                let newTrackName = this.$trackName.cloneNode(),
                    oldTrackName = this.$trackName;

                this.$trackName = newTrackName;
                this.$trackName.innerHTML = trackName;

                oldTrackName.parentNode.prepend(newTrackName);
                newTrackName.classList.add('in');
                oldTrackName.classList.add('out');

                setTimeout(function () {
                    oldTrackName.remove();
                    this.$trackName.classList.remove('in');
                }.bind(this), 400);
            }
        },

        /**
         * Update formatted time.
         * @param {number} totalSeconds
         */
        updateTime: function (totalSeconds) {
            let hours   = ('00' + Math.floor(totalSeconds / 3600)).substr(-2, 2),
                minutes = ('00' + Math.floor(totalSeconds % 3600 / 60)).substr(-2, 2),
                seconds = ('00' + Math.floor(totalSeconds % 60)).substr(-2, 2);

            this.$time.innerText = `${hours}:${minutes}:${seconds}`;
        },

        /**
         * Start/resume audio visualization.
         * Init visualizer if was not yet.
         */
        play: function () {
            if (!visualizer.initialized) {
                visualizer.init(this.$audio, this.$canvas);
            }

            if (this.state !== RUNNING_STATE) {
                this.state = RUNNING_STATE;

                this.run()
            }
        },

        /**
         * Pause audio visualization.
         */
        pause: function () {
            this.state = PAUSED_STATE;

            setTimeout(function () {
                // Timeout is needed to have "fade" effect on canvas
                // Extra state is needed to solve goTo issue for audio element
                if (this.state === PAUSED_STATE) {
                    this.state = STOPPED_STATE;
                }
            }.bind(this), 1000);
        },

        /**
         * Call render and request next frame.
         */
        run: function () {
            visualizer.render();

            if (this.state !== STOPPED_STATE) {
                requestAnimationFrame(this.run.bind(this));
            }
        },

        /**
         * Recalculate canvas size to keep it squared.
         */
        calculateCanvasSize: function () {
            let size;

            if (window.innerWidth > window.innerHeight) {
                size = Math.round(Math.min(window.innerHeight * 0.9, window.innerWidth * 0.4));
                this.element.get(0).classList.remove('vertical');
            } else if (window.innerHeight > window.innerWidth) {
                size = Math.round(Math.min(window.innerWidth * 0.9, window.innerHeight * 0.6));
                this.element.get(0).classList.add('vertical');
            }
            this.$canvas.style.height = size + 'px';
            this.$canvas.style.width = size + 'px';

            this.$canvas.height = size;
            this.$canvas.width = size;
        },

        /**
         * Handle player control buttons.
         * @param {object} event
         */
        handlePlayerControls: function (event) {
            switch (event.key) {
                case ' ':
                    event.preventDefault();

                    if (!this.$audio.paused) {
                        this.$audio.pause();
                    } else {
                        this.$audio.play();
                    }
                    break;
                case 'ArrowLeft':
                    event.preventDefault();

                    this.$audio.currentTime = Math.max(this.$audio.currentTime - 10, 0);
                    break;
                case 'ArrowRight':
                    event.preventDefault();

                    this.$audio.currentTime = Math.min(this.$audio.currentTime + 10, Math.floor(this.$audio.duration));
                    break;
                case 'ArrowUp':
                    event.preventDefault();

                    this.$audio.volume = Math.min(this.$audio.volume + 0.1, 1);
                    break;
                case 'ArrowDown':
                    event.preventDefault();

                    this.$audio.volume = Math.max(this.$audio.volume - 0.1, 0);
                    break;
                case '0':
                    event.preventDefault();

                    this.$audio.currentTime = 0;
                    break;
                case 'f':
                case 'а':
                    event.preventDefault();

                    // @TODO: Implement fullscreen toggling (hiding header and footer) functionality
                    break;
                case 'm':
                case 'ь':
                    event.preventDefault();

                    this.$audio.muted = !this.$audio.muted;
                    break;
            }
        },
    });
});
