<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The MediaContentType enum.
 *
 * @method static self VIDEO()
 *
 * @method static self PHOTO()
 */
class MediaContentType extends Enum
{
    const VIDEO = 'video';

    const PHOTO = 'photo';
}
