<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Invoker;
use Awesome\Frontend\Model\TemplateRenderer;

class Menu extends \Awesome\Frontend\Block\Template
{
    private const SUPPORT_EMAIL_CONFIG = 'support_email_address';
    //@TODO: move this to future Contact module

    /**
     * @var Config $config
     */
    private $config;

    /**
     * Menu constructor.
     * @param TemplateRenderer $renderer
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $children
     * @param array $data
     */
    public function __construct(
        TemplateRenderer $renderer,
        string $nameInLayout,
        ?string $template = null,
        array $children = [],
        array $data = []
    ) {
        parent::__construct($renderer, $nameInLayout, $template, $children, $data);
        $this->config = Invoker::getInstance()->get(Config::class);
    }

    /**
     * Get support email address.
     * @return string
     */
    public function getSupportEmailAddress(): string
    {
        return $this->config->get(self::SUPPORT_EMAIL_CONFIG);
    }
}
