<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPING = 'shipping';
    case COMPLETED = 'completed';
    case DECLINED = 'declined';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'قيد الانتظار',
            self::PROCESSING => 'قيد المعالجة',
            self::SHIPPING => 'قيد الشحن',
            self::COMPLETED => 'مكتمل',
            self::DECLINED => 'ملغي'
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::PROCESSING => 'primary',
            self::SHIPPING => 'warning',
            self::COMPLETED => 'success',
            self::DECLINED => 'danger'
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}