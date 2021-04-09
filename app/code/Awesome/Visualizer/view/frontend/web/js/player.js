define([
    'jquery',
    'Awesome_Visualizer/js/visualizer',
    'jquery/ui',
], function ($, visualizer) {
    'use strict'

    const RUNNING_STATE = 'running',
          PAUSED_STATE  = 'paused',
          STOPPED_STATE = 'stopped';

    $.widget('awesome.player', {
        options: {
            playlistConfig: {},
        },

        audio: null,
        $canvas: null,
        $time: null,
        $name: null,

        filename: null,
        state: null,

        /**
         * Constructor.
         */
        _create: function () {
            this.initFields();
            this.checkTouchScreen();
            this.initBindings();
            this.updateCanvasSize();
        },

        /**
         * Init widget fields.
         */
        initFields: function () {
            this.audio = $('[data-audio]', this.element).get(0);
            this.$canvas = $('[data-canvas]', this.element);
            this.$time = $('[data-time]', this.element);
            this.$name = $('[data-name]', this.element);
        },

        /**
         * Check if screen is touchable and apply respective changes.
         */
        checkTouchScreen: function () {
            if ('ontouchstart' in document.documentElement) {
                $(this.audio).addClass('nohide');
            }
        },

        /**
         * Init event listeners.
         */
        initBindings: function () {
            $(window).on('resize', this.updateCanvasSize.bind(this));

            $(document).on('dragover', function (event) {
                event.preventDefault();
                event.stopPropagation();
            });

            $(document).on('drop', function (event) {
                event.preventDefault();
                event.stopPropagation();

                let file = event.originalEvent.dataTransfer.files[0];

                this.fileName = file.name.replace(/\.[^/.]+$/, '');

                $(this.audio).attr('src', URL.createObjectURL(file));

                this.audio.play();
            }.bind(this));
            // @TODO: Check lock screen play

            $(this.audio).on('timeupdate', function (event) {
                let currentTime = event.currentTarget.currentTime;

                this.updateTrackName(this.fileName, currentTime);
                this.updateTime(currentTime);
            }.bind(this));

            $(this.audio).on('play', this.play.bind(this));

            $(this.audio).on('pause', this.pause.bind(this));

            $(document).on('keyup', function (event) {
                if ($('*:focus').length === 0) {
                    this.handlePlayerControls(event);
                }
            }.bind(this));
        },

        /**
         * Update audio track name.
         * @param {string} trackName
         * @param {number} timeCode
         */
        updateTrackName: function (trackName, timeCode) {
            if (this.options.playlistConfig[trackName]) {
                $.each(this.options.playlistConfig[trackName]['playlist'], function (code, name) {
                    if (code >= timeCode) return false;
                    trackName = name;
                }.bind(this));
            }

            if (this.$name.text() !== trackName) {
                let newTrackName = this.$name.clone(),
                    oldTrackName = this.$name;

                this.$name = newTrackName;
                this.$name.text(trackName);

                oldTrackName.parent().prepend(newTrackName);
                newTrackName.addClass('in');
                oldTrackName.addClass('out');

                setTimeout(function () {
                    oldTrackName.remove();
                    this.$name.removeClass('in');
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

            this.$time.text(`${hours}:${minutes}:${seconds}`);
        },

        /**
         * Start/resume audio visualization.
         * Init visualizer if was not yet.
         */
        play: function () {
            if (this.state !== RUNNING_STATE) {
                if (!visualizer.initialized) {
                    visualizer.init(this.audio, this.$canvas.get(0));
                }

                this.state = RUNNING_STATE;
                this.run();
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
        updateCanvasSize: function () {
            let outerHeight = $(this.element).outerHeight(),
                outerWidth = $(this.element).outerWidth(),
                size;

            if (outerWidth > outerHeight) {
                size = Math.round(Math.min(outerHeight * 0.9, outerWidth * 0.4));
                $(this.element).removeClass('vertical');
            } else if (outerHeight > outerWidth) {
                size = Math.round(Math.min(outerWidth * 0.9, outerHeight * 0.6));
                $(this.element).addClass('vertical');
            }
            this.$canvas.height(size + 'px');
            this.$canvas.width(size + 'px');

            this.$canvas.attr('height', size);
            this.$canvas.attr('width', size);
        },

        /**
         * Handle player control buttons.
         * @param {Object} event
         */
        handlePlayerControls: function (event) {
            switch (event.key) {
                case ' ':
                    event.preventDefault();

                    if (!this.audio.paused) {
                        this.audio.pause();
                    } else {
                        this.audio.play();
                    }
                    break;
                case 'ArrowLeft':
                    event.preventDefault();

                    this.audio.currentTime = Math.max(this.audio.currentTime - 10, 0);
                    break;
                case 'ArrowRight':
                    event.preventDefault();

                    this.audio.currentTime = Math.min(this.audio.currentTime + 10, Math.floor(this.audio.duration));
                    break;
                case '0':
                    event.preventDefault();

                    this.audio.currentTime = 0;
                    break;
                case 'ArrowUp':
                    event.preventDefault();

                    this.audio.volume = Math.min(this.audio.volume + 0.1, 1);
                    break;
                case 'ArrowDown':
                    event.preventDefault();

                    this.audio.volume = Math.max(this.audio.volume - 0.1, 0);
                    break;
                case 'm':
                case 'ь':
                    event.preventDefault();

                    this.audio.muted = !this.audio.muted;
                    break;
                case 'f':
                case 'а':
                    event.preventDefault();

                    // @TODO: Add hiding header/footer functionality along with going fullscreen browser mode
                    break;
                case 'Escape':
                    event.preventDefault();

                    // @TODO: Add exit from fullscreen browser mode (and returning header/footer)
                    break;
                case 'l':
                case 'д':
                    event.preventDefault();

                    // @TODO: Add layout change
                    break;
            }
        },
    });
});
