<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The SizeImage enum.
 *
 * @method static self SMALL()
 * @method static self MIDLE()
 * @method static self LARGE()
 */
class SizeImage extends Enum
{
    const SMALL = '183x183';
    const MIDLE = '369x369';
    const LARGE = '420x420';
}
