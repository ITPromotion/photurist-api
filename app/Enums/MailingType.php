<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The MailingType enum.
 *
 * @method static self ACTIVE
 *
 * @method static self CLOSED
 */
class MailingType extends Enum
{
    const ACTIVE = 'active';

    const CLOSED = 'closed';
}
