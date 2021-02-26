define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict'

    $.widget('awesome.popup', {
        options: {
            closeOnEsc: true,
            confirmButtonSelector: null,
            delayOnPageLoad: 0,
            hideOriginal: true,
            openOnPageLoad: false,
            triggerSelector: null,
        },

        /**
         * Initialized popup window
         */
        popup: null,

        /**
         * Constructor
         */
        _create: function () {
            this.preparePopup();
            this.initBindings();
        },

        /**
         * Init event listeners
         */
        initBindings: function () {
            if (this.options.openOnPageLoad) {
                $(document).ready(function () {
                    setTimeout(this.open.bind(this), this.options.delayOnPageLoad)
                }.bind(this));
            }

            if (this.options.triggerSelector) {
                $(this.options.triggerSelector).on('click', this.open.bind(this));
            }

            if (this.options.confirmButtonSelector) {
                this.popup.on('click', this.options.confirmButtonSelector, this.close.bind(this));
            }
        },

        /**
         * Prepare popup window
         */
        preparePopup: function () {
            if (this.options.hideOriginal) {
                $(this.element).hide();
            }
            this.popup = $('<div class="popup-container" style="display: none;"></div>');

            let $window = $('<div class="popup-window"></div>'),
                $content = $('<div class="popup-content"></div>'),
                $closeButton = $('<button type="button" class="popup-close_button" title="Close"></button>');

            $closeButton.on('click', this.close.bind(this));
            $window.append($closeButton);
            $content.append($(this.element).clone().show());
            $window.append($content);

            this.popup.append($window);
            let $modalWrapper = this.getModalWrapper();

            $modalWrapper.append(this.popup);
        },

        /**
         * Prepare and return modal-wrapper element
         * @returns {jQuery|HTMLElement}
         */
        getModalWrapper: function () {
            let $modalWrapper = $('.modal-wrapper');

            if ($modalWrapper.length !== 1) {
                $modalWrapper = $('<div class="modal-wrapper"></div>');
                $('body').append($modalWrapper)
            }

            return $modalWrapper
        },

        /**
         * Open popup window
         */
        open: function () {
            this.popup.fadeIn();

            $('body').addClass('noscroll');

            if (this.options.closeOnEsc) {
                $(document).one('keyup', function (event) {
                    if (event.key === 'Escape') {
                        this.close();
                    }
                }.bind(this));
            }
            $(this.element).trigger('popup.open');
        },

        /**
         * Close popup window
         */
        close: function () {
            this.popup.fadeOut();

            $('body').removeClass('noscroll');

            $(this.element).trigger('popup.close');
        }
    });
});
