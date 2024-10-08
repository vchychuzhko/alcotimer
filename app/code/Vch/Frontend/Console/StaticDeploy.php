<?php
declare(strict_types=1);

namespace Vch\Frontend\Console;

use Vch\Console\Model\Cli\Input;
use Vch\Console\Model\Cli\Input\InputDefinition;
use Vch\Console\Model\Cli\Output;
use Vch\Framework\Model\Http;
use Vch\Frontend\Model\DeployedVersion;
use Vch\Frontend\Model\StaticContent;

class StaticDeploy extends \Vch\Console\Model\AbstractCommand
{
    /**
     * @var DeployedVersion $deployedVersion
     */
    private $deployedVersion;

    /**
     * @var StaticContent $staticContent
     */
    private $staticContent;

    /**
     * StaticDeploy constructor.
     * @param DeployedVersion $deployedVersion
     * @param StaticContent $staticContent
     */
    public function __construct(
        DeployedVersion $deployedVersion,
        StaticContent $staticContent
    ) {
        $this->deployedVersion = $deployedVersion;
        $this->staticContent = $staticContent;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Generate static files (assets)')
            ->addOption('skip-version', 's', InputDefinition::OPTION_OPTIONAL, 'Skip deployed version generation')
            ->addArgument('view', InputDefinition::ARGUMENT_OPTIONAL, 'Generate static only for provided view');
    }

    /**
     * Generate static files.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output)
    {
        $views = Http::getAllViews();
        $requestedView = $input->getArgument('view');

        if (!is_null($requestedView) ) {
            if (!in_array($requestedView, $views, true)) {
                $output->writeln('Provided view is not registered.');
                $output->writeln();
                $output->writeln('Available views:');
                $output->writeln($output->colourText(implode(', ', $views)), 2);

                throw new \InvalidArgumentException('Invalid view name is provided');
            }

            $views = [$requestedView];
        }

        foreach ($views as $view) {
            $this->staticContent->deploy($view);

            $output->writeln('Static files were deployed for view: ' . $view);
        }

        if (!$input->getOption('skip-version')) {
            $this->deployedVersion->generateVersion();

            $output->writeln('Deployed version was regenerated');
        }
    }
}
