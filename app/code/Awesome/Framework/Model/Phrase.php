<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Locale\Translator;
use Awesome\Framework\Model\Invoker;

class Phrase
{
    /**
     * @var array $arguments
     */
    private $arguments;

    /**
     * @var string $text
     */
    private $text;

    /**
     * @var Translator $translator
     */
    private static $translator;

    /**
     * Phrase construct.
     * @param string $text
     * @param array $arguments
     */
    public function __construct(string $text, array $arguments = [])
    {
        $this->arguments = $arguments;
        $this->text = $text;
    }

    /**
     * Render the phrase.
     * @return string
     */
    public function render(): string
    {
        $text = $this->getText();

        try {
            $text = self::getTranslator()->translate($text);

            if ($arguments = $this->getArguments()) {
                $placeholders = array_map(static function ($key) {
                    return '%' . (is_int($key) ? (string) ($key + 1) : $key);
                }, array_keys($arguments));

                $pairs = array_combine($placeholders, $arguments);
                $text = strtr($text, $pairs);
            }
        } catch (\Exception $e) {}

        return $text;
    }

    /**
     * Perform rendering on string casting.
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Get phrase base text.
     * @return string
     */
    private function getText(): string
    {
        return $this->text;
    }

    /**
     * Get phrase message arguments.
     * @return array
     */
    private function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get a translator, invoking it if not yet.
     * @return Translator
     */
    private static function getTranslator(): Translator
    {
        if (self::$translator === null) {
            self::$translator = Invoker::getInstance()->get(Translator::class);
        }

        return self::$translator;
    }
}
