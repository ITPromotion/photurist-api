<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;

    protected $table = 'otp_status';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->otp = random_int(1000, 9999);

    }

    public function validate($otpCode)
    {
        if ($this->status === 'EXPIRED') {
            return [
                'short_desc' => 'OTP_IS_INCORRECT',
                'long_desc'  => 'Timeout',
            ];
        } elseif ($this->times_checked >= 5) {
            return [
                'short_desc' => 'OTP_IS_INCORRECT',
                'long_desc'  => 'Number of attempts exceeded',
            ];
        } elseif ((time() - strtotime($this->created_at)) > $this->timeout) {
            $this->status = 'EXPIRED';
            $this->save();

            return [
                'short_desc' => 'OTP_IS_INCORRECT',
                'long_desc'  => 'Timeout',
            ];
        } elseif ($this->otp != $otpCode) {
            $this->times_checked++;
            $this->save();

            return [
                'short_desc' => 'OTP_IS_INCORRECT',
                'long_desc'  => 'Code OTP is incorrect',
            ];
        }

        $this->status = 'SUCCESS';
        $this->save();

        return true;
    }

    public function isSuccess(): bool
    {
        return $this->status === 'SUCCESS';

    }
}
