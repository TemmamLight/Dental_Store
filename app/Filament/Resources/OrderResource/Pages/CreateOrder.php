<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\CustomOrderItem;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['order_type'] === 'custom' && isset($data['custom_items'])) {
            foreach ($data['custom_items'] as $item) {
                $customItem = CustomOrderItem::create([
                    'merchant_name' => $item['merchant_name'],
                    'product_name'  => $item['product_name'],
                    'brand'         => $item['brand'],
                    'description'   => $item['description'],
                ]);
                
                $data['items'][] = [
                    'custom_order_item_id' => $customItem->id,
                    'quantity'             => $item['quantity'],
                    'unit_price'           => $item['unit_price'],
                ];
            }
            unset($data['custom_items']);
        }
        
        return $data;
    }


    protected function afterCreate(): void
    {
        $record = $this->record;
        $state = $this->form->getState();

        if ($record->order_type === 'custom' && isset($state['custom_items'])) {
            foreach ($state['custom_items'] as $item) {
                $customItem = CustomOrderItem::create([
                    'merchant_name' => $item['merchant_name'],
                    'product_name'  => $item['product_name'],
                    'brand'         => $item['brand'],
                    'description'   => $item['description'],
                ]);
                
                $record->items()->create([
                    'custom_order_item_id' => $customItem->id,
                    'quantity'             => $item['quantity'],
                    'unit_price'           => $item['unit_price'],
                ]);
            }
        }
    }


}