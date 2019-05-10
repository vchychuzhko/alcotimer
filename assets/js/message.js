;(function ($) {
    $(document).ready(function() {
        window.showMessage = function (message, duration = 5000, isError = false) {
            let $message = $('<p class="message">' + message + '</p>'),
                $container =  $('<span class="message-container' + (isError ? ' error' : '') + '"></span>').append($message);

            $('body').append($container);

            $container.animate({'top': '60px'}, 200);

            let removeMessageTimeout = setTimeout(function () {
                $container.off();
                $container.fadeOut(200, 'linear', function () {
                    $container.remove();
                }.bind(this));
            }.bind(this), duration);

            $container.on('click', function () {
                clearTimeout(removeMessageTimeout);
                $container.off();
                $container.remove();
            }.bind(this));
        }
    })
})(jQuery);
