<?php
declare(strict_types=1);

namespace Vch\Framework\Model;

use Vch\Framework\Helper\DataHelper;
use Vch\Framework\Model\Locale\Translator;
use Vch\Framework\Model\Invoker;

class Phrase
{
    private string $text;

    private array $arguments;

    private static Translator $translator;

    /**
     * Phrase construct.
     * @param string $text
     * @param array $arguments
     */
    public function __construct(
        string $text,
        array $arguments = []
    ) {
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
                $associative = DataHelper::isAssociativeArray($arguments);

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
        if (!isset(self::$translator)) {
            self::$translator = Invoker::getInstance()->get(Translator::class);
        }

        return self::$translator;
    }
}
