define([
    'jquery',
    'Awesome_Visualizer/js/playlist',
    'Awesome_Visualizer/js/visualizer',
    'translator',
    'jquery/ui',
], function ($, playlist, visualizer, __) {
    'use strict'

    const RUNNING_STATE = 'running';
    const PAUSED_STATE  = 'paused';
    const STOPPED_STATE = 'stopped';

    $.widget('awesome.player', {
        options: {
            hideControls: true,
            playlistConfig: {},
            title: null,
        },

        $player: null,

        audio: null,
        $canvas: null,
        $playerControl: null,
        $fullscreenControl: null,
        $shareControl: null,
        $time: null,
        $name: null,

        fileId: null,
        mousemoveTimeout: null,
        state: null,
        stopInterval: null,

        playlist: null,
        visualizer: null,

        /**
         * Constructor.
         */
        _create: function () {
            this._initFields();
            this.updateCanvasSize();
            this._initBindings();
            this._initPlaylist();
            this._initPlayerState();
        },

        /**
         * Init widget fields.
         * @private
         */
        _initFields: function () {
            this.$player = $('[data-player]', this.element);

            this.audio = $('[data-player-audio]', this.element).get(0);
            this.$canvas = $('[data-player-canvas]', this.element);
            this.$playerControl = $('[data-player-control]', this.element);
            this.$fullscreenControl = $('[data-player-fullscreen]', this.element);
            this.$shareControl = $('[data-player-share]', this.element);
            this.$time = $('[data-player-tracktime]', this.element);
            this.$name = $('[data-player-trackname]', this.element);
        },

        /**
         * Check if screen is touchable and add mousemove event to hide controls.
         * @private
         */
        _initControlsHiding: function () {
            if (this.options.hideControls
                && !(('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0))
            ) {
                const hidings = $('.hiding', this.element);

                $(document).on('mousemove', () => {
                    clearTimeout(this.mousemoveTimeout);

                    $('body').css('cursor', '');
                    hidings.removeClass('hide');

                    this.mousemoveTimeout = setTimeout(() => {
                        if (!this.playlist.isOpened() && !$('.hiding:hover', this.element).length) {
                            $('body').css('cursor', 'none');
                            hidings.addClass('hide');
                        }
                    }, 2000);
                });
            }
        },

        /**
         * Init widget event listeners.
         * @private
         */
        _initBindings: function () {
            $(window).on('resize', () => this.updateCanvasSize());

            $(document).on('dragover', (event) => {
                event.preventDefault();
                event.stopPropagation();
            });

            $(document).on('drop', (event) => {
                event.preventDefault();
                event.stopPropagation();

                let file = event.originalEvent.dataTransfer.files[0];

                this._initFile(file.name.replace(/\.[^/.]+$/, ''), URL.createObjectURL(file));

                this.audio.play();
            });

            $(this.audio).on('timeupdate', () => {
                let currentTime = this.audio.currentTime;

                this._updateTrackName(this.fileId, currentTime);
                this._updateTime(currentTime);
            });

            $(this.audio).on('play', () => {
                this.startVisualization();

                this.$playerControl.removeClass(['pause', 'active']).addClass('play');
                this.$playerControl.attr('title', __('Pause') + ' (Space)');
            });

            $(this.audio).on('pause', () => {
                this.stopVisualization();

                this.$playerControl.removeClass('play').addClass(['pause', 'active']);
                this.$playerControl.attr('title', __('Play') + ' (Space)');
            });

            $(this.$playerControl).on('click', () => {
                if (!this.audio.paused) {
                    this.audio.pause();
                } else {
                    this.audio.play();
                }
            });

            $(this.$fullscreenControl).on('click', () => this.toggleFullscreen());

            $(document).on('fullscreenchange', () => {
                if (!document.fullscreenElement) {
                    this.$fullscreenControl.removeClass('active');
                }
            });

            $(this.$shareControl).on('click', () => {
                this.$shareControl.share('open', {
                    url: window.location.origin + window.location.pathname + window.location.search + `#${this.fileId}`,
                    timeCode: this.audio.currentTime,
                });
            });

            $(document).on('keydown', (event) => {
                this._handlePlayerControls(event);

                if ($('*:focus').length === 0 && this.fileId) {
                    this._handleAudioControls(event);
                }
            });
        },

        /**
         * Init player state.
         * @private
         */
        _initPlayerState: function () {
            $(document).ready(() => {
                if (window.location.hash) {
                    let matches = window.location.hash.match(/#(.*?)(\?|$)(.*?t=(\d+)(&|$))?/);

                    if (matches[1] && this.options.playlistConfig[matches[1]]) {
                        let data = this.playlist.getData(matches[1]);

                        this._initFile(matches[1], data.src, data);
                        this._updateTrackName(data.title, -1);
                        this.$playerControl.show();

                        if (matches[4]) {
                            this.audio.currentTime = matches[4];
                        }
                    }
                }
            });
        },

        /**
         * Init player playlist.
         * @private
         */
        _initPlaylist: function () {
            this.playlist = playlist.init($(this.element), this.options.playlistConfig);

            this.playlist.addSelectionCallback((id, data, event) => {
                event.preventDefault();

                this._initFile(id, data.src, data);
                this.audio.play();

                history.replaceState('', document.title, window.location.pathname + window.location.search + `#${id}`);
            });
        },

        /**
         * Initialize playing file.
         * @param {string} id
         * @param {string} src
         * @param {Object} data
         * @private
         */
        _initFile: function (id, src, data = {}) {
            this.fileId = id;
            $(this.audio).attr('src', src);

            let background = data.background || this.playlist.getData(id, 'background');
            this.$player.css('background-image', background ? `url(${background})` : '');

            if (this.options.playlistConfig[id]) {
                this.playlist.setActive(id);
                this.$shareControl.show();
            } else {
                history.replaceState('', document.title, window.location.pathname + window.location.search);
                this.playlist.clearActive();
                this.$shareControl.hide();
            }
        },

        /**
         * Update audio track name.
         * Playlist is used according to the timeCode if possible.
         * @param {string} trackName
         * @param {number} timeCode
         * @private
         */
        _updateTrackName: function (trackName, timeCode) {
            if (this.options.playlistConfig[trackName]) {
                $.each(this.options.playlistConfig[trackName].playlist, (code, name) => {
                    if (code > timeCode) {
                        return false;
                    }

                    trackName = name;
                });
            }

            if (trackName !== this.$name.text()) {
                let oldTrackName = this.$name;

                this.$name = this.$name.clone().text(trackName);
                document.title = trackName + (this.options.title ? ' | ' + this.options.title : '');

                oldTrackName.parent().prepend(this.$name);
                this.$name.addClass('in');
                oldTrackName.addClass('out');

                setTimeout(() => {
                    oldTrackName.remove();
                    this.$name.removeClass('in');
                }, 300);
            }
        },

        /**
         * Format and update elapsed time.
         * @param {number} timeCode
         * @private
         */
        _updateTime: function (timeCode) {
            let hours   = ('00' + Math.floor(timeCode / 3600)).substr(-2);
            let minutes = ('00' + Math.floor(timeCode % 3600 / 60)).substr(-2);
            let seconds = ('00' + Math.floor(timeCode % 60)).substr(-2);

            this.$time.text(`${hours}:${minutes}:${seconds}`);
        },

        /**
         * Start/resume audio visualization.
         * Init visualizer if was not yet.
         */
        startVisualization: function () {
            if (this.state !== RUNNING_STATE) {
                if (!this.visualizer) {
                    this.visualizer = visualizer.init(this.audio, this.$canvas.get(0));
                    this.$playerControl.show();

                    this._initControlsHiding();
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
            this.visualizer.render();

            if (this.state !== STOPPED_STATE) {
                clearInterval(this.stopInterval);
                requestAnimationFrame(() => this._run());
            }
        },

        /**
         * Stop/Pause audio visualization.
         */
        stopVisualization: function () {
            this.state = PAUSED_STATE;

            this.stopInterval = setTimeout(() => {
                // Timeout is needed to have "fade" effect on canvas
                // Extra state is needed to solve goTo issue for audio element
                if (this.state === PAUSED_STATE) {
                    this.state = STOPPED_STATE;
                }
            }, 1000);
        },

        /**
         * Update canvas size attributes.
         */
        updateCanvasSize: function () {
            this.$canvas.attr('height', this.$canvas.height());
            this.$canvas.attr('width', this.$canvas.width());
        },

        /**
         * Handle player control buttons.
         * @param {Object} event
         * @private
         */
        _handlePlayerControls: function (event) {
            switch (event.key) {
                case 'f':
                case 'а':
                    this.toggleFullscreen();
                    // @TODO: Add hiding header/footer functionality, for Esc as well
                    break;
                case 'l':
                case 'д':
                    // @TODO: Add layout change
                    break;
            }
        },

        /**
         * Handle player audio control buttons.
         * @param {Object} event
         * @private
         */
        _handleAudioControls: function (event) {
            switch (event.key) {
                case ' ':
                    if (!this.audio.paused) {
                        this.audio.pause();
                    } else {
                        this.audio.play();
                    }
                    break;
                case 'ArrowLeft':
                    this.audio.currentTime = Math.max(this.audio.currentTime - 10, 0);
                    break;
                case 'ArrowRight':
                    this.audio.currentTime = Math.min(this.audio.currentTime + 10, Math.floor(this.audio.duration));
                    break;
                case '0':
                    this.audio.currentTime = 0;
                    break;
                case 'ArrowUp':
                    this.audio.volume = Math.min(this.audio.volume + 0.1, 1);
                    break;
                case 'ArrowDown':
                    this.audio.volume = Math.max(this.audio.volume - 0.1, 0);
                    break;
                case 'm':
                case 'ь':
                    this.audio.muted = !this.audio.muted;
                    break;
            }
        },

        /**
         * Set or reset fullscreen mode.
         */
        toggleFullscreen: function () {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                this.$fullscreenControl.addClass('active');
            } else if (document.exitFullscreen) {
                document.exitFullscreen();
                this.$fullscreenControl.removeClass('active');
            }
        },
    });
});
