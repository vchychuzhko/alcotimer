<?php

namespace Awesome\Base\Block\Html;

class Body extends \Awesome\Base\Block\Template
{
    private const BODY_TEMPLATE_PATH = '/Awesome/Base/view/base/templates/html/body.phtml';

    protected $template = APP_DIR . self::BODY_TEMPLATE_PATH;
}
