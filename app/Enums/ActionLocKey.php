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
    const GALLERY_DRAFT = 'gallery_draft';
    const TIME_IS_UP = 'time_is_up';
    const POSTCARD_DELETE = 'postcard_delete';
    const WAITING_TIME = 'waiting_time_has_elapsed';
    const POSTCARDS_MAILINGS = 'postcards_mailings';
    const ADDITIONAL_POSTCARD = 'additional_postcard';
    const ADD_CONTACTS = 'add_contacts';
    const REMOV_CONTACTS = 'removed_contacts';


    const GALLERY_TEXT = 'Новая открытка';
    const TIME_IS_UP_TEXT = 'Время рассылки открытки истекло';
    const DELETE_POSTCARD_TEXT = 'Открытка удалена';
    const WAITING_TIME_TEXT = 'Время ожидания открытки истекло';

}
