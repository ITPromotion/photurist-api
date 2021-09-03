<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The PostCardStatus enum.
 *
 * @method static self ACTIVE()
 *
 * @method static self DRAFT()
 *
 * @method static self ARCHIVE()
 */
class PostcardStatus extends Enum
{
    const ACTIVE = 'active';

    const CREATED = 'draft';

    const ARCHIVE = 'archive';
}
