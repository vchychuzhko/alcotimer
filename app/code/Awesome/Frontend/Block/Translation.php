<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

use Awesome\Framework\Model\Locale;
use Awesome\Frontend\Model\Context;

class Translation extends \Awesome\Frontend\Block\Template
{
    /**
     * @var Locale $locale
     */
    private $locale;

    /**
     * Translation constructor.
     * @param Context $context
     * @param Locale $locale
     * @param array $data
     */
    public function __construct(
        Context $context,
        Locale $locale,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
