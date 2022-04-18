<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace allejo\rrlog\Writer;

use Violet\StreamingJsonEncoder\StreamJsonEncoder;

class JsonWriter extends BaseWriter
{
    public function writeTo(string $output): bool
    {
        $json = [
            'header' => $this->replay->getHeader(),
            'startTime' => $this->replay->getStartTime()->format(DATE_ATOM),
            'endTime' => $this->replay->getEndTime()->format(DATE_ATOM),
            'packets' => $this->getPacketIterator(),
        ];

        $fileHandler = fopen($output, 'wb');
        $encoder = new StreamJsonEncoder(
            $json,
            function ($json) use ($fileHandler) {
                fwrite($fileHandler, $json);
            }
        );

        $encoder->setOptions(JSON_PRETTY_PRINT);
        $successful = true;

        try
        {
            $encoder->encode();
        }
        catch (\Exception $e)
        {
            $successful = false;
        }

        fclose($fileHandler);

        return $successful;
    }

    private function getPacketIterator(): \Generator
    {
        foreach ($this->replay->getPacketsIterable() as $packet)
        {
            if ($this->shouldWritePacket($packet))
            {
                yield $packet;
            }
        }
    }
}
