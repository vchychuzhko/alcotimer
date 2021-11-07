<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Http\Request;

class Locale implements \Awesome\Framework\Model\SingletonInterface
{
    public const LOCALE_COOKIE = 'locale_code';
    public const DEFAULT_LOCALE = 'en_US';

    private const DEFAULT_LOCALE_CONFIG = 'default_locale';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var string $locale
     */
    private $locale = self::DEFAULT_LOCALE;

    /**
     * Locale constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Initialize current locale according to request cookie.
     * @param Request $request
     * @return $this
     */
    public function init(Request $request): self
    {
        $locale = $request->getCookie(self::LOCALE_COOKIE) ?: $this->config->get(self::DEFAULT_LOCALE_CONFIG);

        if ($locale) {
            try {
                $this->setLocale($locale);
            } catch (\RuntimeException $e) {}
        }

        return $this;
    }

    /**
     * Get current locale code.
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get all defined locale codes.
     * @return array
     */
    public static function getAllLocales(): array
    {
        return [
            'en_US',
            'ru_RU',
            'uk_UA',
        ];
    }

    /**
     * Set locale for current session, it must be registered.
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        if (!in_array($locale, self::getAllLocales(), true)) {
            throw new \InvalidArgumentException(__('Cannot set unregistered locale: %1', $locale));
        }
        $this->locale = $locale;

        return $this;
    }
}
