<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The DeviceType enum.
 *
 * @method static self IOS()
 * @method static self ANDROID()
 */
class DeviceType extends Enum
{
    const IOS = 'ios';
    const ANDROID = 'android';
}
