<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

interface GeneratorInterface
{
    /**
     * Generate static file for provided view.
     * @param string $path
     * @param string $view
     * @return $this
     */
    public function generate(string $path, string $view): self;

    /**
     * Check if provided file path can be processed with this generator.
     * @param string $path
     * @return bool
     */
    public static function match(string $path): bool;
}
