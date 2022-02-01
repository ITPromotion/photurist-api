<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The ClientStatus enum.
 *
 * @method static self ACTIVE()
 *
 * @method static self BLOCK()
 *
 * @method static self IGNORE()
 */
class ContactStatuses extends Enum
{
    const ACTIVE = 'active';

    const BLOCK = 'block';

    const IGNORE = 'ignore';
}
