<?php
declare(strict_types=1);

namespace Vch\Framework\Model;

use Vch\Framework\Model\Http\Request;

class Locale
{
    public const LOCALE_COOKIE = 'locale';

    public const EN_LOCALE = 'en_US';
    public const UA_LOCALE = 'uk_UA';

    private const DEFAULT_LOCALE_CONFIG = 'default_locale';

    private Config $config;

    private ?Request $request;

    private string $locale;

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
     * Get current locale code.
     * @return string
     */
    public function getLocale(): string
    {
        if (!isset($this->locale)) {
            $locale = $this->getRequest() ? $this->getRequest()->getCookie(self::LOCALE_COOKIE) : null;

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

    /**
     * Get Request object if not CLI environment.
     * @return Request|null
     */
    private function getRequest(): ?Request
    {
        if (!isset($this->request)) {
            $this->request = PHP_SAPI !== 'cli' ? Request::getInstance() : null;
        }

        return $this->request;
    }
}
