<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The ClientStatus enum.
 *
 * @method static self ACTIVE()
 *
 * @method static self BLOCK()
 */
class ClientStatus extends Enum
{
    const ACTIVE = 'active';

    const BLOCK = 'block';
}
