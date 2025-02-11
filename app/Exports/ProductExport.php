<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products->map(function ($product) {
            return [
                'المنتج' => $product->name,
                'الماركة' => $product->brand->name ?? 'غير محدد',
                'الكمية المباعة' => $product->total_sold ?? 0,
                'السعر' => $product->price,
                'الوصف' => $product->description,
                'النوع' => $product->orderItems()
                    ->whereHas('order', function ($query) {
                        $query->where('status', \App\Enums\OrderStatusEnum::COMPLETED->value);
                    })
                    ->first() ? \App\Enums\OrderStatusEnum::COMPLETED->value : 'غير محدد',
            ];
        });
    }

    public function headings(): array
    {
        return ['المنتج', 'الماركة', 'الكمية المباعة', 'السعر', 'الوصف', 'النوع'];
    }
}