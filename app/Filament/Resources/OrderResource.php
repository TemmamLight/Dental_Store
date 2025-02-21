<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 3;

    
    protected function getTableQuery()
    {
        return parent::getTableQuery()->with(['items.product', 'items.custom_order_item']);
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', '=', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $totalPending = static::getModel()::where('status', 'pending')->count();
        
        return $totalPending > 15 ? 'danger' 
            : ($totalPending > 5 ? 'warning' 
            : 'success');
    }

    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                Forms\Components\Wizard\Step::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->default('OR-' . random_int(100000, 999999))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('shipping_price')
                            ->label('Shipping Cost')
                            ->default(0)
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(OrderStatusEnum::options())
                            ->required(),
                        Forms\Components\Select::make('order_type')
                            ->options([
                                'regular' => 'طلب عادي',
                                'custom' => 'طلب خاص'
                            ])
                            ->live()
                            ->required()
                            ->reactive(),
                        Forms\Components\MarkdownEditor::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Wizard\Step::make('Order Items')
                    ->schema([
                        Forms\Components\Section::make('Regular Order Items')
                            ->schema([
                                Forms\Components\Repeater::make('items') 
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Product')
                                            ->options(Product::pluck('name', 'id'))
                                            ->searchable()
                                            ->reactive()
                                            ->required()
                                            ->afterStateUpdated(function ($state, $set) {
                                                    $set('unit_price', Product::find($state)?->price ?? 0);
                                                }),
                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required(),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->numeric()
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->visible(fn ($get)=> $get('order_type') === 'regular'),
                                ]),
                        Forms\Components\Section::make('Custom Order Items')
                            ->schema([
                                Forms\Components\Repeater::make('custom_items')
                                    ->schema([
                                        Forms\Components\TextInput::make('merchant_name')
                                            ->label('Merchant Name')
                                            ->required(),
                                        Forms\Components\TextInput::make('product_name')
                                            ->label('Product Name')
                                            ->required(),
                                        Forms\Components\TextInput::make('brand')
                                            ->label('brand')
                                            ->nullable(),
                                        Forms\Components\Textarea::make('description')
                                            ->label('description Product')
                                            ->nullable(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('unit Price')
                                            ->numeric()
                                            ->required()
                                    ])
                                        ->columns(3)
                                        ->visible(fn ($get) => $get('order_type') === 'custom'),
                            ]),
                    ])
            ])->columnSpanFull()
        ]);
    }




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Order status')
                    ->formatStateUsing(fn(string $state) => OrderStatusEnum::tryFrom($state)?->label()?? $state)
                    ->color(function ($state) {
                            return match($state) {
                                'pending' => 'gray',
                                'processing' => 'primary',
                                'shipping' => 'warning',
                                'completed' => 'success',
                                'declined' => 'danger',
                                default => 'gray'
                            };
                        })
                    ->icon(function ($state) {
                            return match($state) {
                                'pending' => 'heroicon-o-clock',
                                'processing' => 'heroicon-o-cog',
                                'shipping' => 'heroicon-o-truck',
                                'completed' => 'heroicon-o-check-circle',
                                'declined' => 'heroicon-o-x-circle',
                                default => 'heroicon-o-question-mark-circle'
                            };
                        })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_type')
                    ->label('Order Type')
                    ->sortable()
                    ->searchable()
                    ->icon(fn($state) => $state === 'regular' ? 'heroicon-o-shopping-bag' : 'heroicon-o-cube')
                    ->formatStateUsing(function ($state) {
                            return $state === 'regular' ? 'Regular' : 'Custom';
                        })
                        ->color(function ($state) {
                            return $state === 'regular' ? 'success' : 'warning';
                        }),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_price')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label("Order Data")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('items.product.name')
                    ->label('Regular Product')
                    ->toggleable(isToggledHiddenByDefault:true),

                Tables\Columns\TextColumn::make('items.custom_order_item.product_name')
                    ->label('Custom Product')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Pending Orders')
                    ->options([
                        'pending' => 'Pending Orders', 
                        'processing' => 'Processing Orders',
                        'shipping' => 'Shipping Orders',
                        'completed' => 'Completed Orders',
                        'declined' => 'Declined Orders',
                    ]),
                Tables\Filters\SelectFilter::make('order_type')
                    ->options([
                        'regular' => 'Regqular Order',
                        'custom' => 'Custom Order',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}