<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The ActionLocKey enum.
 *
 * @method static self GALLERY()
 * @method static self TIME_IS_UP()
 * @method static self POSTCARD_DELETE()
 *  @method static self WAITING_TIME()
 */
class ActionLocKey extends Enum
{
    const GALLERY = 'gallery';
    const TIME_IS_UP = 'time_is_up';
    const POSTCARD_DELETE = 'postcard_delete';
    const WAITING_TIME = 'waiting_time_has_elapsed';
}
