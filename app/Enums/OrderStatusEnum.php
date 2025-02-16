<?php 

namespace App\Enums;

enum OrderStatusEnum : string {
    
    case PENDING = 'pending';       // بانتظار الموافقة
    case PROCESSING = 'processing'; // قيد التحضير
    case SHIPPING = 'shipping';     // قيد التوصيل
    case COMPLETED = 'completed';   // تم التوصيل
    case DECLINED = 'declined';     // مرفوض

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'بانتظار الموافقة',
            self::PROCESSING => 'قيد التحضير',
            self::SHIPPING => 'قيد التوصيل',
            self::COMPLETED => 'تم التوصيل',
            self::DECLINED => 'مرفوض',
        };
    }
}