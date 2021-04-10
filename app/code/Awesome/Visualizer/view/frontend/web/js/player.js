define([
    'jquery',
    'Awesome_Visualizer/js/playlist',
    'Awesome_Visualizer/js/visualizer',
    'jquery/ui',
], function ($, playlist, visualizer) {
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

        fileId: null,
        playlist: {},
        state: null,

        /**
         * Constructor.
         */
        _create: function () {
            this._initFields();
            this.checkTouchScreen();
            this._initBindings();
            this.updateCanvasSize();
            this._initPlaylist();
        },

        /**
         * Init widget fields.
         * @private
         */
        _initFields: function () {
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
                $(this.audio).addClass('visible');
            }
        },

        /**
         * Init widget event listeners.
         * @private
         */
        _initBindings: function () {
            $(window).on('resize', () => this.updateCanvasSize());

            $(document).on('dragover', function (event) {
                event.preventDefault();
                event.stopPropagation();
            });

            $(document).on('drop', (event) => {
                event.preventDefault();
                event.stopPropagation();

                let file = event.originalEvent.dataTransfer.files[0];

                this.playFile(file.name.replace(/\.[^/.]+$/, ''), URL.createObjectURL(file));
            });
            // @TODO: Check lock screen play

            $(this.audio).on('timeupdate', (event) => {
                let currentTime = event.currentTarget.currentTime;

                this._updateTrackName(this.fileId, currentTime);
                this._updateTime(currentTime);
            });

            $(this.audio).on('play', () => this.startVisualization());

            $(this.audio).on('pause', () => this.stopVisualization());

            $(document).on('keyup', (event) => {
                if ($('*:focus').length === 0) {
                    this._handlePlayerControls(event);
                }
            });
        },

        /**
         * Init player playlist.
         * @private
         */
        _initPlaylist: function () {
            playlist.init($(this.element), this.options.playlistConfig);

            playlist.addSelectionCallback((id, data) => {
                this.playFile(id, data.src, data);
            });
        },

        /**
         * Initialize and start playing file.
         * @param {string} id
         * @param {string} src
         * @param {Object} data
         * @private
         */
        playFile: function (id, src, data = {}) {
            this.fileId = id;
            $(this.audio).attr('src', src);

            let background = data.background || playlist.getData(id, 'background');
            $(this.element).css('background-image', background ? `url(${background})` : '');

            this.playlist[id] = data.playlist || playlist.getData(id, 'playlist');

            this.audio.play();
        },

        /**
         * Update audio track name.
         * Playlist is used according to the timeCode if possible.
         * @param {string} trackName
         * @param {number} timeCode
         * @private
         */
        _updateTrackName: function (trackName, timeCode) {
            if (this.playlist[trackName]) {
                $.each(this.playlist[trackName], (code, name) => {
                    if (code > timeCode) {
                        return false;
                    }

                    trackName = name;
                });
            }

            if (trackName !== this.$name.text()) {
                let newTrackName = this.$name.clone(),
                    oldTrackName = this.$name;

                this.$name = newTrackName;
                this.$name.text(trackName);

                oldTrackName.parent().prepend(newTrackName);
                newTrackName.addClass('in');
                oldTrackName.addClass('out');

                setTimeout(() => {
                    oldTrackName.remove();
                    this.$name.removeClass('in');
                }, 300);
            }
        },

        /**
         * Update formatted time.
         * @param {number} timeCode
         * @private
         */
        _updateTime: function (timeCode) {
            let hours   = ('00' + Math.floor(timeCode / 3600)).substr(-2, 2),
                minutes = ('00' + Math.floor(timeCode % 3600 / 60)).substr(-2, 2),
                seconds = ('00' + Math.floor(timeCode % 60)).substr(-2, 2);

            this.$time.text(`${hours}:${minutes}:${seconds}`);
        },

        /**
         * Start/resume audio visualization.
         * Init visualizer if was not yet.
         */
        startVisualization: function () {
            if (this.state !== RUNNING_STATE) {
                if (!visualizer.initialized) {
                    visualizer.init(this.audio, this.$canvas.get(0));
                }

                this.state = RUNNING_STATE;
                this._run();
            }
        },

        /**
         * Call render and request next frame.
         * @private
         */
        _run: function () {
            visualizer.render();

            if (this.state !== STOPPED_STATE) {
                requestAnimationFrame(() => this._run());
            }
        },

        /**
         * Stop/Pause audio visualization.
         */
        stopVisualization: function () {
            this.state = PAUSED_STATE;

            setTimeout(() => {
                // Timeout is needed to have "fade" effect on canvas
                // Extra state is needed to solve goTo issue for audio element
                if (this.state === PAUSED_STATE) {
                    this.state = STOPPED_STATE;
                }
            }, 1000);
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
         * @private
         */
        _handlePlayerControls: function (event) {
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
                case 'p':
                case 'з':
                    event.preventDefault();

                    playlist.togglePlaylist();
                    break;
            }
        },
    });
});
