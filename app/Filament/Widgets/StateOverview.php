<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StateOverview extends BaseWidget
{
    protected static ?string $polingInterval = '60s';
    protected static bool $isLazy = true;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers ', Customer::count())
                ->description('Increase in customers')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),  
            Stat::make('Total Product ', Product::count())
                ->description('Total  Product in app')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),  
            Stat::make('Panding Orders ', Order::where('status', OrderStatusEnum::PENDING->value)->count())
                ->description('Total  Order  in app ')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),  
        ];
    }
}