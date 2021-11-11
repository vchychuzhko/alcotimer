define([
    'jquery',
    'notification',
    'api/clipboard',
    'translator',
    'jquery/ui',
    'modal',
], function ($, notification, clipboard, __) {
    'use strict'

    $.widget('awesome.share', $.awesome.modal, {
        options: {
            buttons: [{
                attributes: {},
                class: 'button button--primary',
                text: __('Done'),

                /**
                 * Default action on button click.
                 */
                click: function () {
                    this.close();
                },
            }],
            maxWidth: '480px',
            showMessage: true,
            title: __('Share a link'),
            queryParameter: 't',
        },

        $url: null,
        $timecode: null,

        url: '',
        timeCode: 0,

        /**
         * Init widget fields.
         * @private
         */
        _initFields: function () {
            this._super();

            this.$url = $('[data-share-url]', this.$modal);
            this.$timecode = $('[data-share-timecode]', this.$modal);
        },

        /**
         * @inheritDoc
         */
        _initBindings: function () {
            this._super();

            this.$timecode.on('change', () => {
                let url = this.$timecode.prop('checked')
                    ? this.url + `?${this.options.queryParameter}=${this.$timecode.val()}`
                    : this.url;

                this.$url.val(url);
            });

            $('[data-share-copy]', this.$modal).on('click', () => {
                clipboard.copy(this.$url.val(), this.options.showMessage);
            });
        },

        /**
         * @inheritDoc
         */
        _getContent: function () {
            return $(`
<div class="share">
    <div class="share__urlbar">
        <input class="share__url" name="url" type="text" value="" title="${__('Url to share')}" data-share-url disabled>
        <button class="share__copy" type="button" data-share-copy>${__('Copy')}</button>
    </div>
    <div class="share__timebar" data-share-timebar>
        <label class="label">
            <input class="checkbox" name="timecode" type="checkbox" value="" data-share-timecode>
            <span data-share-timelabel></span>
        </label>
    </div>
</div>
`);
        },

        /**
         * Open share window.
         * @param {object} payload
         */
        open: function ({ url, timeCode }) {
            this.url = url;
            this.timeCode = Math.floor(timeCode);

            this.$url.val(url);
            this.$timecode.prop('checked', false);

            if (this.timeCode) {
                $('[data-share-timelabel]', this.$modal).text(__('Start with %1', this._getTimeFormatted(this.timeCode)));
                this.$timecode.val(this.timeCode);
                $('[data-share-timebar]', this.$modal).show();
            } else {
                $('[data-share-timebar]', this.$modal).hide();
            }

            this._super();
        },

        /**
         * Format elapsed time.
         * @param {number} timeCode
         * @returns {string}
         * @private
         */
        _getTimeFormatted: function (timeCode) {
            let hours   = Math.floor(timeCode / 3600);
            let minutes = hours ? ('00' + Math.floor(timeCode % 3600 / 60)).substr(-2) : Math.floor(timeCode % 3600 / 60);
            let seconds = ('00' + timeCode % 60).substr(-2);

            return `${hours ? hours + ':' : ''}${minutes}:${seconds}`;
        },
    });
});
