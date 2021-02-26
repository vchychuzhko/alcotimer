<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Http\Request;

class Locale implements \Awesome\Framework\Model\SingletonInterface
{
    public const LOCALE_COOKIE = 'locale_code';
    public const DEFAULT_LOCALE = 'uk_UA';

    private const ALLOWED_LOCALES = [
        'en_US',
        'uk_UA',
        'ru_RU',
    ];

    /**
     * @var string $locale
     */
    private $locale = self::DEFAULT_LOCALE;

    /**
     * Initialize current locale according to request cookie.
     * @param Request $request
     * @return $this
     */
    public function init(Request $request): self
    {
        if ($locale = $request->getCookie(self::LOCALE_COOKIE)) {
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
     * Get all registered locale codes.
     * @return array
     */
    public function getAllLocales(): array
    {
        return self::ALLOWED_LOCALES;
    }

    /**
     * Set locale code, checking if it's registered.
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        if (!in_array($locale, self::ALLOWED_LOCALES, true)) {
            throw new \RuntimeException(sprintf('Cannot set not registered locale: %s', $locale));
        }
        $this->locale = $locale;

        return $this;
    }
}
