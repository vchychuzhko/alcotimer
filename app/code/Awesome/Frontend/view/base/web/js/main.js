require([
    'jquery',
], function ($) {
    'use strict'

    const dataAttribute  = 'data-awesome-init';
    const nodeSelector   = '[' + dataAttribute + ']';
    const scriptSelector = 'script[type="text/x-awesome-init"]';

    let virtuals = [];

    /**
     * Initialize components assigned to a specified element via data-* attribute.
     *
     * @param {HTMLElement} element - Element to initialize components with.
     * @param {Object|string} config - Initial components' config.
     * @param {string} component - Components' path.
     */
    function init(element, config, component) {
        require([component], function (fn) {
            if (typeof fn === 'object') {
                fn = fn[component].bind(fn);
            }

            if (typeof fn === 'function') {
                fn(config, element);
            } else if ($(element)[component]) {
                $(element)[component](config);
            }
        }, function (error) {
            if ('console' in window && typeof window.console.error === 'function') {
                console.error(error);
            }

            return true;
        });
    }

    /**
     * Parse elements 'data-awesome-init' attribute as a valid JSON data.
     * Note: 'data-awesome-init' attribute will be removed.
     *
     * @param {HTMLElement} element - Element whose attribute should be parsed.
     * @returns {Object}
     */
    function getData(element) {
        let data = $(element).attr(dataAttribute);

        $(element).removeAttr(dataAttribute);

        return {
            element: element,
            data:    JSON.parse(data)
        };
    }
    /**
     * Add components to the virtual list.
     *
     * @param {Object} components
     */
    function addVirtual(components) {
        virtuals.push({
            element: false,
            data:    components
        });
    }

    /**
     * Merge provided data with a current data of a elements' "data-awesome-init" attribute.
     *
     * @param {Object} components - Object with components and theirs configuration.
     * @param {HTMLElement} element - Element whose data should be modified.
     */
    function setData(components, element) {
        let data = $(element).attr(dataAttribute);

        data = $.extend(true, data ? JSON.parse(data) : {}, components);

        $(element).attr(dataAttribute, JSON.stringify(data));
    }

    /**
     * Search for the elements by provided selector and extends theirs data.
     *
     * @param {Object} components - Object with components and theirs configuration.
     * @param {string} selector - Selector for the elements.
     */
    function processElements(selector, components) {
        if (selector === '*') {
            addVirtual(components);
        } else {
            $.each($(selector), function (index, item) {
                setData(components, item);
            });
        }
    }

    /**
     * Parse content of a provided script node.
     * Note: Node will be removed from DOM.
     *
     * @param {HTMLScriptElement} node - Node to be processed.
     * @returns {Object}
     */
    function getNodeData(node) {
        let data = $(node).text();

        $(node).remove();

        return JSON.parse(data);
    }

    /**
     * Parse 'script' tags with a custom type attribute and moves its data
     * to a 'data-awesome-init' attribute of an element found by provided selector.
     * Note: All found script nodes will be removed from DOM.
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
     * @example Providing data virtually without selector.
     *      {
     *          "*": {
     *              "path/to/component": {"bar": "baz"}
     *          }
     *      }
     */
    function processScripts() {
        $.each($.map($(scriptSelector), getNodeData), function (index, item) {
            $.each(item, processElements);
        });
    }

    /**
     * Initialize components assigned to HTML elements via [data-awesome-init].
     * Note: All found attribute declarations will be removed from DOM.
     *
     * @example Sample 'data-awesome-init' declaration.
     *      data-awesome-init='{"path/to/component": {"foo": "bar"}}'
     */
    $(function () {
        processScripts();

        $.each(
            $.merge($.map($(nodeSelector), getData), virtuals),
            function (index, itemContainer) {
                $.each(itemContainer.data, function (obj, key) {
                    init(itemContainer.element, key, obj);
                });
            }
        );
    })
});
