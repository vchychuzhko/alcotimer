define([
    'jquery',
], function ($) {
    'use strict'

    class Playlist {
        playlist;

        $playlist;
        $playlistControl;

        /**
         * Player playlist constructor.
         * @param {jQuery} $context
         * @param {Object} playlistConfig
         */
        constructor($context, playlistConfig) {
            this.playlist = playlistConfig;

            this._initFields($context);
            this._initBindings();
        }

        /**
         * Initialize playlist fields.
         * @param {jQuery} $context
         * @private
         */
        _initFields ($context) {
            this.$playlistControl = $('[data-playlist-control]', $context);
            this.$playlist = $('[data-playlist]', $context);
        }

        /**
         * Initialize playlist listeners.
         * @private
         */
        _initBindings () {
            this.$playlistControl.on('click', () => this.togglePlaylist());

            $(document).on('click', (event) => {
                if (!$(event.target).closest(this.$playlist).length) {
                    this.closePlaylist();
                }
            });

            $(document).on('keyup', (event) => this._handlePlaylistControls(event));
        }

        /**
         * Open/Close playlist menu according to its state.
         */
        togglePlaylist () {
            if (this.isOpened()) {
                this.closePlaylist();
            } else {
                this.openPlaylist();
            }
        }

        /**
         * Check current playlist state.
         */
        isOpened () {
            return this.$playlist.hasClass('opened');
        }

        /**
         * Close playlist menu.
         */
        closePlaylist () {
            this.$playlist.removeClass('opened');
            this.$playlistControl.removeClass('active');
        }

        /**
         * Open playlist menu.
         */
        openPlaylist () {
            this.$playlist.addClass('opened');
            this.$playlistControl.addClass('active');
        }

        /**
         * Attach a callback on track selection.
         * Callable can accept these parameters:
         *      'id' - string containing file code
         *      'data' - object with all track data
         *      'event' - click event object
         * @param {function} callback
         */
        addSelectionCallback (callback) {
            $('[data-playlist-track]', this.$playlist).on('click', (event) => {
                let id = $(event.currentTarget).data('track-id');

                callback(id, this.getData(id), event);
            });
        }

        /**
         * Set playlist item as active by id.
         * @param {string} id
         */
        setActive (id) {
            this.clearActive();
            $('[data-track-id="' + id + '"]', this.$playlist).addClass('active');
        }

        /**
         * Reset playlist active items.
         */
        clearActive () {
            $('[data-playlist-track]', this.$playlist).removeClass('active');
        }

        /**
         * Retrieve track data by id and key.
         * Return all data if key is not specified
         * @param {string} id
         * @param {string} key
         * @returns {Object|null}
         */
        getData (id, key = '') {
            let data = this.playlist[id] || null;

            if (data && key !== '') {
                data = data[key] || null;
            }

            return data;
        }

        /**
         * Handle playlist control buttons.
         * @param {Object} event
         * @private
         */
        _handlePlaylistControls (event) {
            switch (event.key) {
                case 'Escape':
                    this.closePlaylist();
                    break;
                case 'p':
                case 'з':
                    this.togglePlaylist();
                    break;
            }
        }
    }

    return {
        /**
         * Initialize player playlist with registered audio tracks.
         * @param {jQuery} $context
         * @param {Object} playlistConfig
         * @returns {Playlist}
         */
        init: function ($context, playlistConfig) {
            return new Playlist($context, playlistConfig);
        }
    }
});
