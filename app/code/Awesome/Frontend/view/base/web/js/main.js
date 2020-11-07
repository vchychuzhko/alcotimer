require([
    'jquery',
], function ($) {
    'use strict';

    const dataAttr = 'data-awesome-init',
          nodeSelector = '[' + dataAttr + ']',
          scriptSelector = 'script[type="text/x-awesome-init"]';

    let virtuals = [];

    /**
     * Initializes components assigned to a specified element via data-* attribute.
     *
     * @param {HTMLElement} elem - Element to initialize components with.
     * @param {Object|String} config - Initial components' config.
     * @param {String} component - Components' path.
     */
    function init(elem, config, component) {
        require([component], function (fn) {
            if (typeof fn === 'object') {
                fn = fn[component].bind(fn);
            }

            if (typeof fn === 'function') {
                fn(config, elem);
            } else if ($(elem)[component]) {
                $(elem)[component](config);
            }
        }, function (error) {
            if ('console' in window && typeof window.console.error === 'function') {
                console.error(error);
            }

            return true;
        });
    }

    /**
     * Parses elements 'data-awesome-init' attribute as a valid JSON data.
     * Note: data-awesome-init attribute will be removed.
     *
     * @param {HTMLElement} elem - Element whose attribute should be parsed.
     * @returns {Object}
     */
    function getData(elem) {
        let data = $(elem).attr(dataAttr);

        $(elem).removeAttr(dataAttr);

        return {
            elem: elem,
            data: JSON.parse(data)
        };
    }
    /**
     * Adds components to the virtual list.
     *
     * @param {Object} components
     */
    function addVirtual(components) {
        virtuals.push({
            elem: false,
            data: components
        });
    }

    /**
     * Merges provided data with a current data
     * of a elements' "data-awesome-init" attribute.
     *
     * @param {Object} components - Object with components and theirs configuration.
     * @param {HTMLElement} elem - Element whose data should be modified.
     */
    function setData(components, elem) {
        let data = $(elem).attr(dataAttr);

        data = data ? JSON.parse(data) : {};

        data = $.extend(true, data, components);
        data = JSON.stringify(data);
        $(elem).attr(dataAttr, data);
    }

    /**
     * Search for the elements by provided selector and extends theirs data.
     *
     * @param {Object} components - Object with components and theirs configuration.
     * @param {String} selector - Selector for the elements.
     */
    function processElems(selector, components) {
        if (selector === '*') {
            addVirtual(components);
        } else {
            $.each($(selector), function (index, item) {
                setData(components, item);
            });
        }
    }

    /**
     * Parses content of a provided script node.
     * Note: node will be removed from DOM.
     *
     * @param {HTMLScriptElement} node - Node to be processed.
     * @returns {Object}
     */
    function getNodeData(node) {
        let data = node.textContent;

        node.parentNode.removeChild(node);

        return JSON.parse(data);
    }

    /**
     * Parses 'script' tags with a custom type attribute and moves it's data
     * to a 'data-awesome-init' attribute of an element found by provided selector.
     * Note: All found script nodes will be removed from DOM.
     *
     * @returns {Array} An array of components not assigned to the specific element.
     *
     * @example Sample declaration.
     *      <script type="text/x-awesome-init">
     *          {
     *              "body": {
     *                  "path/to/component": {"foo": "bar"}
     *              }
     *          }
     *      </script>
     *
     * @example Providing data without selector.
     *      {
     *          "*": {
     *              "path/to/component": {"bar": "baz"}
     *          }
     *      }
     */
    function processScripts(context) {
        let nodes = $(scriptSelector, context);

        $.each($.map(nodes, getNodeData), function (index, item) {
            $.each(item, processElems);
        });

        return virtuals.splice(0, virtuals.length);
    }

    /**
     * Initializes components assigned to HTML elements via [data-awesome-init].
     *
     * @example Sample 'data-awesome-init' declaration.
     *      data-awesome-init='{"path/to/component": {"foo": "bar"}}'
     */
    (function (context) {
        let virtuals = processScripts(context ? context : document),
            nodes = $(nodeSelector);

        $.each(
            $.merge($.map(nodes, getData), virtuals),
            function (index, itemContainer) {
                let element = itemContainer.elem;

                $.each(itemContainer.data, function (obj, key) {
                    init.call(null, element, key, obj);
                });
            }
        );
    })(document)
});
