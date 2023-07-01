<?php

namespace App\Enum;

class BookingStatusEnum
{
    public const PENDING = 0;
    public const PAID = 1;
    public const CANCELLED = 2;

    public static function getChoices(): array {
        return [
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled'
        ];
    }
}