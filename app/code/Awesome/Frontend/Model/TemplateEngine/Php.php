<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\TemplateEngine;

use Awesome\Frontend\Model\BlockInterface;

class Php
{
    /**
     * @var BlockInterface $block
     */
    private $block;

    /**
     * Include named template using the given block.
     * Prevents incorrect $this usage.
     * @param BlockInterface $block
     * @param string $fileName
     * @param array $dictionary
     * @return string
     * @throws \Exception
     */
    public function render(BlockInterface $block, string $fileName, array $dictionary = []): string
    {
        ob_start();

        try {
            $this->block = $block;
            extract($dictionary, EXTR_SKIP);

            include $fileName;
        } catch (\Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Redirect methods calls to the current block.
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return call_user_func_array([$this->block, $method], $args);
    }
}
