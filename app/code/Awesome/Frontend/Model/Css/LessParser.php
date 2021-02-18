<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Css;

use Less_Parser as WikimediaParser;

/**
 * Wrapper for Wikimedia less parser.
 * @see \Less_Parser
 * @link https://github.com/wikimedia/less.php
 */
class LessParser
{
    /**
     * @var WikimediaParser $parser
     */
    private $parser;

    /**
     * LessParser constructor.
     * @param WikimediaParser $parser
     */
    public function __construct(WikimediaParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Wrap Wikimedia ModifyVars method.
     * @param array $variables
     * @return void
     */
    public function setVariables(array $variables): void
    {
        $this->parser->ModifyVars($variables);
    }

    /**
     * Enable source map for result css.
     * @return void
     */
    public function enableSourceMap(): void
    {
        $this->parser->SetOptions(['sourceMap' => true, 'sourceMapBasepath' => APP_DIR]);
    }

    /**
     * Wrap Wikimedia parseFile method.
     * @param string $file
     * @return void
     */
    public function addFile(string $file): void
    {
        $this->parser->parseFile($file);
    }

    /**
     * Wrap Wikimedia getCss method.
     * @return string
     */
    public function getCss(): string
    {
        return $this->parser->getCss();
    }

    /**
     * Wrap Wikimedia Reset method.
     * @return void
     */
    public function reset(): void
    {
        $this->parser->Reset();
    }
}
