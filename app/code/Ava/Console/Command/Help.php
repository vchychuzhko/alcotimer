<?php

namespace Ava\Console\Command;

class Help extends \Ava\Console\AbstractCommand
{
    /**
     * Clean static files.
     * @return string
     */
    public function show()
    {
        return "--- AlcoTimer CLI ---\n"
            . "Here is the list of available commands:\n"
            . $this->colourText('php bin/console cache:clean') . " | Clean and regenerate static files, forcing browser to reload JS and CSS.\n"
            . "\n"
            . $this->colourText('php bin/console maintenance:enable [--ip=<ip address>]') . " | Enable maintenance mode with list of allowed ids.\n"
            . $this->colourText('php bin/console maintenance:disable') . " | Disable maintenance mode.\n"
            . $this->colourText('php bin/console maintenance:status') . " | View current state of maintenance.";
    }
}
