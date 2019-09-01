<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace allejo\rrlog;

class Filesystem
{
    public static function expand_tilde($path)
    {
        if (function_exists('posix_getuid') && strpos($path, '~') !== false)
        {
            $info = posix_getpwuid(posix_getuid());
            $path = str_replace('~', $info['dir'], $path);
        }

        return $path;
    }
}
