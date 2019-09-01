<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace allejo\rrlog\Writer;

use allejo\bzflag\networking\Packets\GamePacket;
use allejo\bzflag\networking\Replay;

interface IReplayWriter
{
    public function __construct(Replay $replay);

    /**
     * @param string[] $blacklist
     *
     * @throws \InvalidArgumentException
     */
    public function setBlacklist(array $blacklist): void;

    /**
     * @param string[] $whitelist
     *
     * @throws \InvalidArgumentException
     */
    public function setWhitelist(array $whitelist): void;

    public function shouldWritePacket(GamePacket $packet): bool;

    public function writeTo(string $output): bool;
}
