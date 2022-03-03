<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Http\Request;

class Locale implements \Awesome\Framework\Model\SingletonInterface
{
    public const LOCALE_COOKIE = 'locale';

    public const EN_LOCALE = 'en_US';
    public const UA_LOCALE = 'uk_UA';

    private const DEFAULT_LOCALE_CONFIG = 'default_locale';

    private Config $config;

    private Request $request;

    private string $locale;

    /**
     * Locale constructor.
     * @param Config $config
     * @param Request $request
     */
    public function __construct(
        Config $config,
        Request $request
    ) {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Get current locale code.
     * @return string
     */
    public function getLocale(): string
    {
        if (!isset($this->locale)) {
            $locale = $this->request->getCookie(self::LOCALE_COOKIE);

            if (!$locale || !in_array($locale, self::getAllLocales(), true)) {
                $locale = (string) $this->config->get(self::DEFAULT_LOCALE_CONFIG);
            }

            $this->locale = $locale;
        }

        return $this->locale;
    }

    /**
     * Get all defined locale codes.
     * @return array
     */
    public static function getAllLocales(): array
    {
        return [
            self::EN_LOCALE,
            self::UA_LOCALE,
        ];
    }
}
