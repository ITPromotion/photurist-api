<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The Video enum.
 *
 * @method static self START()
 * @method static self DURATION()
 * @method static self FRAME()
 */
class Video extends Enum
{
    const START = 1;
    const DURATION = 5;
    const FRAME = 15;
}
