<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Html;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Invoker;
use Awesome\Frontend\Model\TemplateRenderer;

class Header extends \Awesome\Frontend\Block\Template
{
    private const LOGO_CONFIG_PATH = 'web/logo';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * Header constructor.
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
     * Get app logo file path.
     * @return string
     */
    public function getLogo(): string
    {
        return $this->getMediaFileUrl($this->config->get(self::LOGO_CONFIG_PATH));
    }

    /**
     * Check if current page is a Homepage.
     * @return bool
     */
    public function isHomepage(): bool
    {
        return in_array(Http::ROOT_ACTION_NAME, $this->renderer->getHandles(), true);
    }
}
