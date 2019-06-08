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
            . $this->colourText('php bin/console cache:clean', 'green') . " | Clean and regenerate static files, forcing browser to reload JS and CSS.\n"
            . "\n"
            . $this->colourText('php bin/console maintenance:enable [--ip=<ip address>]', 'green') . " | Enable maintenance mode with list of allowed ids.\n"
            . $this->colourText('php bin/console maintenance:disable', 'green') . " | Disable maintenance mode.\n"
            . $this->colourText('php bin/console maintenance:status', 'green') . " | View current state of maintenance.";
    }
}
