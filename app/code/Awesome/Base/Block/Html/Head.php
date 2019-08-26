<?php

namespace Awesome\Base\Block\Html;

class Head extends \Awesome\Base\Block\Template
{
    private const HEAD_TEMPLATE_PATH = '/Awesome/Base/view/base/templates/html/head.phtml';

    /**
     * @inheritDoc
     */
    public function toHtml() {
        return include(APP_DIR . self::HEAD_TEMPLATE_PATH);
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->data['title']['text'] ?? '';
    }

    /**
     *
     * @return array
     */
    public function getScripts()
    {
        return $this->data['links']['scripts'] ?? [];
    }

    /**
     *
     * @return array
     */
    public function getStyles()
    {
        return $this->data['links']['csss'] ?? [];
    }
}
