<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The UserStatus enum.
 *
 * @method static self 'CREATED'()
 */
class UserStatus extends Enum
{
    const CREATED = 'created';

    const ACTIVE = 'active';

    const BLOCKED = 'blocked';
}
