<?php

namespace Awesome\Base\Block\Html;

class Head extends \Awesome\Base\Block\Template
{
    private const HEAD_TEMPLATE_PATH = '/Awesome/Base/view/base/templates/html/head.phtml';

    protected $template = APP_DIR . self::HEAD_TEMPLATE_PATH;
}
