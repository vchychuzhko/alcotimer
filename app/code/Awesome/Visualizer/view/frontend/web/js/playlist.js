define([
    'jquery',
], function ($) {
    'use strict'

    class Playlist {
        _playlistConfig;

        _$playlist;
        _$playlistToggle;

        /**
         * Player playlist constructor.
         * @param {jQuery} $context
         * @param {Object} playlistConfig
         */
        constructor($context, playlistConfig) {
            this._playlistConfig = playlistConfig;

            this._initFields($context);
            this._initBindings();
        }

        /**
         * Initialize playlist fields.
         * @param {jQuery} $context
         * @private
         */
        _initFields ($context) {
            this._$playlistToggle = $('[data-playlist-control]', $context);
            this._$playlist = $('[data-playlist]', $context);
        }

        /**
         * Initialize playlist listeners.
         * @private
         */
        _initBindings () {
            this._$playlistToggle.on('click', () => this.togglePlaylist());

            $(document).on('click', (event) => {
                if (!$(event.target).closest(this._$playlist).length) {
                    this.togglePlaylist(false);
                }
            });
        }

        /**
         * Open/Close playlist menu.
         * State can be forced.
         * @param {boolean|null} open
         */
        togglePlaylist (open = null) {
            open = open !== null ? open : !this._$playlist.is('.active');

            if (open) {
                this._$playlistToggle.addClass('active');
                this._$playlist.addClass('active');
            } else {
                this._$playlistToggle.removeClass('active');
                this._$playlist.removeClass('active');
            }
        }

        /**
         * Attach a callback on track selection.
         * Callable can accept two parameters:
         *      'id' - string containing file code,
         *      'data' - object with all track data.
         * @param {function} callback
         */
        addSelectionCallback (callback) {
            $('[data-playlist-track]', this._$playlist).on('click', (event) => {
                let id = $(event.currentTarget).data('track-id');

                callback(id, this.getData(id));
            });
        }

        /**
         * Retrieve track data by id and key.
         * Return all data if key is not specified
         * @param {string} id
         * @param {string} key
         * @returns {Object|null}
         * @private
         */
        getData (id, key = '') {
            let data = this._playlistConfig[id] || null;

            if (data && key !== '') {
                data = data[key] || null;
            }

            return data;
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
