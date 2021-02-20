<?php
declare(strict_types=1);

namespace Awesome\Framework\Exception;

use Awesome\Framework\Model\Phrase;

class LocalizedException extends \Exception
{
    /**
     * @var Phrase|string $phrase
     */
    protected $phrase;

    /**
     * LocalizedException constructor.
     * @param Phrase|string $phrase
     * @param int $code
     * @param \Throwable|null $cause
     */
    public function __construct($phrase, int $code = 0, ?\Throwable $cause = null)
    {
        parent::__construct((string) $phrase, $code, $cause);
        $this->phrase = $phrase;
    }

    /**
     * Get un-processed message, without filled parameters.
     * @return string
     */
    public function getRawMessage(): string
    {
        return is_string($this->phrase) ? $this->phrase : $this->phrase->getText();
    }
}
