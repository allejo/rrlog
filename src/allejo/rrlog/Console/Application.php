<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace allejo\rrlog\Console;

use allejo\rrlog\Command\ExportCommand;

class Application extends \Symfony\Component\Console\Application
{
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), [
            new ExportCommand(),
        ]);
    }
}
