<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The ResponseStatuses enum.
 *
 * @method static self SUCCESS()
 */
class ResponseStatuses extends Enum
{
    const SUCCESS = 200;

    const CREATED = 201;
}
