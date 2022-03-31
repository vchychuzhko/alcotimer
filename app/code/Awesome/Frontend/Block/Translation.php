<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

use Awesome\Framework\Model\Locale;
use Awesome\Frontend\Model\DeployedVersion;
use Awesome\Frontend\Model\Layout;

class Translation extends \Awesome\Frontend\Block\Template
{
    private Locale $locale;

    /**
     * Translation constructor.
     * @param DeployedVersion $deployedVersion
     * @param Layout $layout
     * @param Locale $locale
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $data
     */
    public function __construct(
        DeployedVersion $deployedVersion,
        Layout $layout,
        Locale $locale,
        string $nameInLayout,
        ?string $template = null,
        array $data = []
    ) {
        parent::__construct($deployedVersion, $layout, $nameInLayout, $template, $data);
        $this->locale = $locale;
    }

    /**
     * Get current locale code.
     * @return string
     */
    public function getLocaleCode(): string
    {
        return $this->locale->getLocale();
    }
}
