define([
    'dictionary',
], function (dictionary) {
    'use strict'

    return function (text, ...args) {
        text = dictionary[text] || text;

        if (args.length) {
            let pairs = args;

            if (!Array.isArray(args[0]) && typeof args[0] === 'object') {
                pairs = args[0];
            } else {
                if (Array.isArray(args[0])) {
                    pairs = args[0];
                }

                pairs = pairs.reduce((result, item, index) => ({
                    ...result,
                    [index + 1]: item,
                }), {});
            }

            text = text.replace(
                RegExp(`%(${Object.keys(pairs).join('|')})`, 'g'),
                placeholder => pairs[placeholder.replace(/^%/, '')]
            );
        }

        return text;
    }
});
