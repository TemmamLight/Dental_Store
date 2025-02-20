<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load([
            'items.product',
            'items.custom_order_item'
        ]);

        $data =  $this->record->toArray();
        // dd($data['items']);
        return $data;
    }
    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Details')
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->label('Order Number')
                        ->disabled(),
                    Forms\Components\TextInput::make('customer.name')
                        ->label('Customer')
                        ->disabled(),
                    Forms\Components\TextInput::make('status')
                        ->label('Order Status')
                        ->disabled(),
                    Forms\Components\TextInput::make('shipping_price')
                        ->label('Shipping Cost')
                        ->disabled(),
                    Forms\Components\MarkdownEditor::make('notes')
                        ->label('Notes')
                        ->disabled(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Regular Order Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->schema([
                            Forms\Components\TextInput::make('product.name')
                                ->label('Product')
                                ->disabled(),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantity')
                                ->disabled(),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->disabled(),
                        ])
                        ->columns(3)
                        ->visible(fn ($get) => $get('order_type') === 'regular'),
                ]),

            Forms\Components\Section::make('Custom Order Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->schema([
                            Forms\Components\TextInput::make('custom_order_item.product_name')
                                ->label('Product Name')
                                ->disabled(),
                            Forms\Components\TextInput::make('custom_order_item.merchant_name')
                                ->label('Merchant Name')
                                ->disabled(),
                            Forms\Components\TextInput::make('custom_order_item.brand')
                                ->label('Brand')
                                ->disabled(),
                            Forms\Components\Textarea::make('custom_order_item.description')
                                ->label('Description')
                                ->disabled(),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantity')
                                ->disabled(),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->disabled(),
                        ])
                        ->columns(3)
                        ->visible(fn ($get) => $get('order_type') === 'custom'),
                ]),
        ]);
    }

}