<?php
declare(strict_types=1);

namespace Vch\Frontend\Model\Css;

use Less_Parser as WikimediaParser;

/**
 * Wrapper for Wikimedia less parser.
 * @see \Less_Parser
 * @link https://github.com/wikimedia/less.php
 */
class LessParser
{
    private WikimediaParser $parser;

    /**
     * LessParser constructor.
     * @param WikimediaParser $parser
     */
    public function __construct(
        WikimediaParser $parser
    ) {
        $this->parser = $parser;
    }

    /**
     * Wrap Wikimedia ModifyVars method.
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        $this->parser->ModifyVars($variables);
    }

    /**
     * Enable source map for result css.
     */
    public function enableSourceMap()
    {
        $this->parser->SetOptions(['sourceMap' => true, 'sourceMapBasepath' => APP_DIR]);
    }

    /**
     * Wrap Wikimedia parseFile method.
     * @param string $file
     */
    public function addFile(string $file)
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
     */
    public function reset()
    {
        $this->parser->Reset();
    }
}
