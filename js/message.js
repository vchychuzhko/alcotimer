;(function ($) {
    $(document).ready(function() {
        window.showMessage = function (message, duration = 5000, isError = false) {
            let $message = $('<p class="message">' + message + '</p>'),
                $container =  $('<span class="message-container' + (isError ? ' error' : '') + '"></span>').append($message);

            $('body').append($container);

            $container.animate({'top': '10px'}, 200);

            let removeMessage = setTimeout(function () {
                $container.off();
                $container.fadeOut(200, 'linear', function () {
                    $container.remove();
                }.bind(this));
            }.bind(this), duration);

            $container.on('click', function () {
                clearTimeout(removeMessage);
                $container.off();
                $container.remove();
            }.bind(this));
        }
    })
})(jQuery);
