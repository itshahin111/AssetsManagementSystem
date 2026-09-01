<?php

namespace App\Enums;

enum AssetTrackingType: string
{
    case Individual = 'individual';
    case Quantity   = 'quantity';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'individual' => 'Individual',
            'quantity'   => 'Quantity',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
