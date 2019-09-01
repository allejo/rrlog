<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace allejo\rrlog\Writer;

use allejo\bzflag\networking\Packets\GamePacket;
use allejo\bzflag\networking\Packets\NetworkMessage;
use allejo\bzflag\networking\Replay;

abstract class BaseWriter implements IReplayWriter
{
    /** @var array<string, int> */
    protected static $supportedPackets;

    /** @var Replay */
    protected $replay;

    /** @var string[] */
    protected $blacklist;

    /** @var bool */
    protected $blacklistMode;

    /** @var string[] */
    protected $whitelist;

    /** @var bool */
    protected $whitelistMode;

    public function __construct(Replay $replay)
    {
        $this->replay = $replay;
        $this->blacklist = [];
        $this->blacklistMode = false;
        $this->whitelist = [];
        $this->whitelistMode = false;

        $this->loadSupportedPackets();
    }

    /**
     * @param string[] $blacklist
     *
     * @throws \InvalidArgumentException
     */
    public function setBlacklist(array $blacklist): void
    {
        $this->whitelistMode = false;
        $this->blacklistMode = true;

        $this->validatePackets($blacklist, $this->blacklist);
    }

    /**
     * @param string[] $whitelist
     *
     * @throws \InvalidArgumentException
     */
    public function setWhitelist(array $whitelist): void
    {
        $this->blacklistMode = false;
        $this->whitelistMode = true;

        $this->validatePackets($whitelist, $this->whitelist);
    }

    public function shouldWritePacket(GamePacket $packet): bool
    {
        if (!$this->blacklistMode && !$this->whitelistMode)
        {
            return true;
        }

        $inArray = in_array($packet->getRawPacket()->getCode(), self::$supportedPackets);

        return $this->whitelistMode ? $inArray : !$inArray;
    }

    /**
     * @param array $incoming
     * @param array $validated
     *
     * @throws \InvalidArgumentException
     */
    private function validatePackets(array $incoming, array &$validated): void
    {
        $errors = [];

        foreach ($incoming as $packet)
        {
            if (!isset(self::$supportedPackets[$packet]))
            {
                $errors[] = $packet;

                continue;
            }

            $validated[] = $packet;
        }

        if (!empty($errors))
        {
            throw new \InvalidArgumentException(sprintf('Following invalid packets were found: %s', implode(', ', $errors)));
        }
    }

    private function loadSupportedPackets(): void
    {
        if (!empty(self::$supportedPackets))
        {
            return;
        }

        $networkClass = new \ReflectionClass(NetworkMessage::class);
        $constants = $networkClass->getConstants();

        foreach ($constants as $constant => $value)
        {
            $name = strtolower($constant);
            $chunks = explode('_', $name);

            foreach ($chunks as &$chunk)
            {
                $chunk = ucfirst($chunk);
            }

            $packetName = sprintf('Msg%s', implode('', $chunks));

            self::$supportedPackets[$packetName] = $value;
        }
    }
}
