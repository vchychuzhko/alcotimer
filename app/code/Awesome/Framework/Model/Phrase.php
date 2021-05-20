<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Locale\Translator;
use Awesome\Framework\Model\Invoker;

class Phrase
{
    /**
     * @var string $text
     */
    private $text;

    /**
     * @var array $arguments
     */
    private $arguments;

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
        $this->text = $text;
        $this->arguments = $arguments;
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
                $associative = array_keys($arguments) !== range(0, count($arguments) - 1);

                $placeholders = array_map(static function ($placeholder) use ($associative) {
                    return '%' . ($associative ? $placeholder : ($placeholder + 1));
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
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get phrase message arguments.
     * @return array
     */
    public function getArguments(): array
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
