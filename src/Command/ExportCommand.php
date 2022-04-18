<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace allejo\rrlog\Command;

use allejo\bzflag\networking\Packets\PacketInvalidException;
use allejo\bzflag\networking\Replay;
use allejo\rrlog\Filesystem;
use allejo\rrlog\Writer\IReplayWriter;
use allejo\rrlog\Writer\JsonWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('export')
            ->setDescription('Export a BZFlag replay file into a text file')
            ->addArgument('replay', InputArgument::REQUIRED, 'The replay file')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The format to export the replay to', 'json')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'The output file', '')
            ->addOption('blacklist', null, InputOption::VALUE_OPTIONAL, 'A list of packets to ignore in the export. This conflicts with the `whitelist` option.', '')
            ->addOption('whitelist', null, InputOption::VALUE_OPTIONAL, 'A list of packets to only include. This conflicts with the `blacklist` option.', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $blacklist = $input->getOption('blacklist');
        $whitelist = $input->getOption('whitelist');
        $blacklistUsed = !empty($blacklist);
        $whitelistUsed = !empty($whitelist);

        if ($blacklistUsed && $whitelistUsed)
        {
            $output->writeln('You cannot use both a packet whitelist and blacklist.');

            return 1;
        }

        $origFilePath = $input->getArgument('replay');
        $replayFilePath = Filesystem::expand_tilde($origFilePath);

        if (!file_exists($replayFilePath))
        {
            $output->writeln(sprintf('File does not exist: %s', $origFilePath));

            return 1;
        }

        /** @var null|Replay $replay */
        $replay = null;

        try
        {
            $replay = new Replay($replayFilePath);
        }
        catch (PacketInvalidException $e)
        {
            $output->writeln('An invalid or corrupted replay file was given.');

            return 2;
        }

        $writerClass = $this->getWriter($input->getOption('format'));

        /** @var IReplayWriter $writer */
        $writer = new $writerClass($replay);

        try
        {
            if ($blacklistUsed)
            {
                $writer->setBlacklist($this->expandAliases($blacklist));
            }

            if ($whitelistUsed)
            {
                $writer->setWhitelist($this->expandAliases($whitelist));
            }
        }
        catch (\InvalidArgumentException $e)
        {
            $output->writeln(sprintf('Invalid packet found: %s', $e->getMessage()));

            return 3;
        }

        /** @var null|string $outputFile */
        $outputFile = $input->getOption('output');

        if (!$outputFile)
        {
            $outputFile = $replayFilePath . '.json';
        }

        $success = $writer->writeTo($outputFile);

        $output->writeln('Done!');

        return $success ? 0 : 4;
    }

    private function getWriter(string $format): string
    {
        $writers = [
            'json' => JsonWriter::class,
        ];

        return $writers[$format] ?? $writers['json'];
    }

    /**
     * Expand out aliases for packet names that the core bzflag-networking.php
     * automatically transforms.
     *
     * For example, the core library transforms all `MsgPlayerUpdateSmall`
     * packets into `MsgPlayerUpdate` automatically.
     *
     * @return array A list of packet names that has had aliases resolved
     */
    private function expandAliases(string $packetListCSV): array
    {
        $packets = explode(',', $packetListCSV);

        if (in_array('MsgPlayerUpdate', $packets) && !in_array('MsgPlayerUpdateSmall', $packets))
        {
            $packets[] = 'MsgPlayerUpdateSmall';
        }

        return $packets;
    }
}
