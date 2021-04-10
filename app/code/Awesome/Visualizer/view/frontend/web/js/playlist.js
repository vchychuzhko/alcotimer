define([
    'jquery',
], function ($) {
    'use strict'

    return {
        playlistConfig: {},

        $playlist: null,
        $playlistToggle: null,

        /**
         * Initialize player playlist with registered audio tracks.
         * @param {jQuery} $context
         * @param {Object} playlistConfig
         */
        init: function ($context, playlistConfig) {
            this.playlistConfig = playlistConfig;

            this._initFields($context);
            this._initBindings();
            // @TODO: Make play on page load by hash in URL, use <a> link for this
        },

        /**
         * Initialize playlist fields.
         * @param {jQuery} $context
         * @private
         */
        _initFields: function ($context) {
            this.$playlistToggle = $('[data-playlist-toggle]', $context);
            this.$playlist = $('[data-playlist]', $context);
        },

        /**
         * Initialize playlist listeners.
         * @private
         */
        _initBindings: function () {
            this.$playlistToggle.on('click', () => this.togglePlaylist());

            $(document).on('click', (event) => {
                if (!$(event.target).closest(this.$playlist).length) {
                    this.togglePlaylist(false);
                }
            });
        },

        /**
         * Open/Close playlist menu.
         * @param {boolean|null} open
         */
        togglePlaylist: function (open = null) {
            open = open !== null ? open : !this.$playlist.is('.active');

            if (open) {
                this.$playlistToggle.addClass('active');
                this.$playlist.addClass('active');
            } else {
                this.$playlistToggle.removeClass('active');
                this.$playlist.removeClass('active');
            }
        },

        /**
         * Attach a callback on track selection.
         * Callable can accept two parameters:
         *      'id' - string containing file code,
         *      'data' - object with all track data.
         * @param {function} callback
         */
        addSelectionCallback: function (callback) {
            $('[data-track-id]', this.$playlist).on('click', (event) => {
                let id = $(event.currentTarget).data('track-id');

                callback(id, this.getData(id));
            });
        },

        /**
         * Retrieve track data by id and key.
         * Return all data if key is not specified
         * @param {string} id
         * @param {string} key
         * @returns {Object|null}
         * @private
         */
        getData: function (id, key = '') {
            let data = this.playlistConfig[id] || null;

            if (data && key !== '') {
                data = data[key] || null;
            }

            return data;
        },
    }
});
