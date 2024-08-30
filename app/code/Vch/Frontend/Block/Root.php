<?php
declare(strict_types=1);

namespace Vch\Frontend\Block;

use Vch\Framework\Model\Locale;
use Vch\Frontend\Model\DeployedVersion;
use Vch\Frontend\Model\Layout;

class Root extends \Vch\Frontend\Block\Template
{
    private const DEFAULT_LANGUAGE = 'en';
    private const LANGUAGE_MAP = [
        'en_US' => 'en',
        'uk_UA' => 'uk',
    ];

    private Locale $locale;

    protected ?string $template = 'Vch_Frontend::root.phtml';

    private string $language;

    /**
     * Root constructor.
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
     * Get current page language code.
     * @return string
     */
    public function getLanguage(): string
    {
        if (!isset($this->language)) {
            $locale = $this->locale->getLocale();

            $this->language = self::LANGUAGE_MAP[$locale] ?? self::DEFAULT_LANGUAGE;
        }

        return $this->language;
    }
}
