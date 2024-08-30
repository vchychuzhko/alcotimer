<?php
declare(strict_types=1);

namespace Vch\Frontend\Model;

interface GeneratorInterface
{
    /**
     * Generate static file for provided view returning its content.
     * @param string $path
     * @param string $view
     * @return string
     */
    public function generate(string $path, string $view): string;

    /**
     * Check if provided file path can be processed with this generator.
     * @param string $path
     * @return bool
     */
    public static function match(string $path): bool;
}
