<?php
declare(strict_types=1);

namespace Awesome\Frontend\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Model\StaticContent;

class StaticDeploy extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var StaticContent $staticContent
     */
    private $staticContent;

    /**
     * StaticDeploy constructor.
     * @param StaticContent $staticContent
     */
    public function __construct(StaticContent $staticContent)
    {
        $this->staticContent = $staticContent;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Generate static files (assets)')
            ->addArgument('view', InputDefinition::ARGUMENT_OPTIONAL, 'Generate static only for a specified view');
    }

    /**
     * Generate static files.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output): void
    {
        $definedViews = [Http::FRONTEND_VIEW, Http::BACKEND_VIEW];
        $view = $input->getArgument('view');

        if ($view) {
            if (!in_array($view, $definedViews, true)) {
                $output->writeln('Provided view was not recognized.');
                $output->writeln();
                $output->writeln('Available views:');
                $output->writeln($output->colourText(implode(', ', $definedViews)), 2);

                throw new \InvalidArgumentException('Invalid view name is provided');
            }

            $this->staticContent->deploy($view);
            $output->writeln(sprintf('Static content was deployed for "%s" view.', $view));
        } else {
            $this->staticContent->deploy();
            $output->writeln('Static content was deployed for views: ' . implode(', ', $definedViews));
        }
    }
}
