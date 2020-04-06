;(function ($) {
    $.widget('awesome.popup', {
        options: {
            closeOnEsc: true,
            delayOnPageLoad: 0,
            hideOriginal: true,
            openOnPageLoad: false,
            triggerSelector: null,
            showOverlay: true
        },

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
        },

        /**
         * Prepare popup element
         */
        preparePopup: function () {
            if (this.options.hideOriginal) {
                $(this.element).hide();
            }
            this.popup = $('<div class="popup-container" style="display: none;"></div>');

            if (this.options.showOverlay) {
                this.popup.addClass('has-overlay');
            }

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
         * @return {jQuery|HTMLElement}
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

            if (this.options.showOverlay) {
                $('body').addClass('noscroll');
            }

            if (this.options.closeOnEsc) {
                $(document).on('keydown.popup', function (event) {
                    if (event.keyCode === 27) { // ESC
                        this.close();
                        $(document).off('keydown.popup');
                    }
                }.bind(this));
            }
        },

        /**
         * Close popup window
         */
        close: function () {
            this.popup.fadeOut();

            if (this.options.showOverlay) {
                $('body').removeClass('noscroll');
            }
        }
    });
})(jQuery);
