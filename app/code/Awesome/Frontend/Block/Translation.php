<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

use Awesome\Framework\Model\Locale;
use Awesome\Frontend\Model\DeployedVersion;

class Translation extends \Awesome\Frontend\Block\Template
{
    /**
     * @var Locale $locale
     */
    private $locale;

    /**
     * Translation constructor.
     * @param DeployedVersion $deployedVersion
     * @param Locale $locale
     * @param array $data
     */
    public function __construct(
        DeployedVersion $deployedVersion,
        Locale $locale,
        array $data = []
    ) {
        parent::__construct($deployedVersion, $data);
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
