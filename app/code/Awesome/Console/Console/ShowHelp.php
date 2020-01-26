<?php

namespace Awesome\Console\Console;

class ShowHelp extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var \Awesome\Console\Model\XmlParser\CliXmlParser $xmlParser
     */
    private $xmlParser;

    /**
     * ShowHelp constructor.
     * @inheritDoc
     */
    public function __construct($options = [], $arguments = [])
    {
        $this->xmlParser = new \Awesome\Console\Model\XmlParser\CliXmlParser();
        parent::__construct($options, $arguments);
    }

    /**
     * Show help with app version or list of all available commands.
     * @inheritDoc
     */
    public function execute()
    {
        $output = $this->getAppCliTitle() . "\n\n"
            . $this->colourText('Usage:', 'brown') . "\n"
            . '  ' . 'command [options] [arguments]' . "\n\n";

        //@TODO: Implement command options functionality
        $options = [
            '-h, --help' => 'Display this help message',
            '-q, --quiet' => 'Do not output any message',
            '-v, --version' => 'Display this application version',
//            '-n, --no-interaction' => 'Do not ask any interactive questions'
        ];

        if ($options) {
            $output .= $this->colourText('Options:', 'brown') . "\n";

            foreach ($options as $name => $description) {
                $output .= '  ' . str_pad($this->colourText($name), 35) . $description . "\n";
            }
            $output .= "\n";
        }

        if ($commandList = $this->xmlParser->retrieveConsoleCommands()) {
            $output .= $this->colourText('Available commands:', 'brown') . "\n";

            foreach ($commandList as $namespace => $commands) {
                $output .= ' ' . $this->colourText($namespace, 'brown') . "\n";

                foreach ($commands as $name => $command) {
                    if (!$command['disabled']) {
                        $output .= '  ' . str_pad($this->colourText($namespace . ':' . $name), 40) . $command['description'] . "\n";
                    }
                }
            }
        } else {
            $output .= 'No commands are currently available.';
        }

        return $output;
    }

    /**
     * Get application CLI title with version.
     * @return string
     */
    public function getAppCliTitle()
    {
        return 'AlcoTimer CLI ' . $this->colourText(\Awesome\Framework\Model\App::VERSION);
    }
}
