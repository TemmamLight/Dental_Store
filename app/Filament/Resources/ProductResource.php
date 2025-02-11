<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Enums\ProductTypeEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationGroup='Shop';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 20;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Brand' => $record->brand->name,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('brand');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur:true)
                            ->unique()
                            ->afterStateUpdated(function(string $operation, $state, Forms\Set $set){
                                if ($operation !== 'create'){
                                    return ;
                                }

                                $set('slug', Str::slug($state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord:true),
                        Forms\Components\MarkdownEditor::make('description')->columnSpan('full'),
                        Forms\Components\Section::make('Pricing & Inventory')
                            ->schema([
                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU (Stock Keeping Unit)')
                                    ->unique()
                                    ->required(),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->rules('regex:/^\d{1,6}(\,\d{0,2})?$/')
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->required(),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'downloadable'=> ProductTypeEnum::DOWNLOADABLE->value,
                                        'deliverable'=> ProductTypeEnum::DELIVERABLE->value,
                                    ])->required(),
                            ])->columns(2),
                    ])->columns(2),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                        ->schema([
                            Forms\Components\Toggle::make('is_visible')
                                ->label('Visibility')
                                ->helperText('Enable or disable product visibility')
                                ->default(true),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('Featured')
                                ->helperText('Enable or disable products featured status'),
                            Forms\Components\DatePicker::make('published_at')
                                ->label('Availability')
                                ->default(now())
                        ]),
                        Forms\Components\Section::make('Image')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->directory('form-attachments')
                                    ->preserveFilenames()
                                    ->image()
                                    ->imageEditor()
                                    ->default(fn ($record) => $record?->image), // عرض الصورة الحالية
                            ])->collapsible(), 
                        Forms\Components\Section::make('Associations')
                            ->schema([
                                Forms\Components\Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->required(),
                                Forms\Components\Select::make('category_id')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->required(),
                            ]), 
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('published_at')
                    ->date()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->date()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->headerActions([
                Action::make('export_top_selling')
                ->label('تصدير المنتجات الأكثر مبيعًا')
                ->form([
                    Select::make('time_period')
                        ->label('حدد الفترة الزمنية')
                        ->options([
                            'today' => 'اليوم',
                            'this_week' => 'هذا الأسبوع',
                            'this_month' => 'هذا الشهر',
                            'this_year' => 'هذه السنة',
                            'all' => 'الكل',
                        ])
                        ->default('all'),
                    Select::make('limit')
                        ->label('عدد المنتجات')
                        ->options([
                            '5' => '5 منتجات',
                            '10' => '10 منتجات',
                            '20' => '20 منتج',
                            '50' => '50 منتج',
                        ])
                        ->default('10'),
                ])
                ->action(fn (array $data) => self::exportTopSellingProducts($data['time_period'], $data['limit']))
                ->color('success'),
            ])
            ->filters([
                SelectFilter::make('time_price')
                    ->label('Filter by Time Period')
                    ->options([
                        'today' => 'اليوم',
                        'this_week' => 'هذا الأسبوع',
                        'this_month' => 'هذا الشهر',
                        'this_year' => 'هذه السنة',
                        'all' => 'الكل',
                    ])
                    ->default('all')
                    ->query(fn ($query, $state) => self::applyTimeFilter($query, $state)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make(),
                ]),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    // دالة لتطبيق فلتر الفترة الزمنية على الاستعلام
    public static function applyTimeFilter($query, $timePeriod)
    {
        if ($timePeriod === 'today') {
            return $query->whereDate('created_at', Carbon::today());
        } elseif ($timePeriod === 'this_week') {
            return $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($timePeriod === 'this_month') {
            return $query->whereMonth('created_at', Carbon::now()->month);
        } elseif ($timePeriod === 'this_year') {
            return $query->whereYear('created_at', Carbon::now()->year);
        }
        return $query;
    }

    // دالة لجلب المنتجات الأكثر مبيعًا
    public static function getTopSellingProducts($timePeriod, $limit = 10)
    {
        return Product::withCount(['orderItems as total_sold' => function ($query) use ($timePeriod) {
            $query->whereHas('order', function ($query) use ($timePeriod) {
                $query->where('status', OrderStatusEnum::COMPLETED->value);
                self::applyTimeFilter($query, $timePeriod);
            });
        }])
        ->having('total_sold', '>', 0) // تصفية المنتجات التي لديها مبيعات مكتملة فقط
        ->orderByDesc('total_sold')
        ->take($limit)
        ->get();
    }

    // دالة لتصدير المنتجات
    public static function exportTopSellingProducts($timePeriod, $limit)
    {
        $products = self::getTopSellingProducts($timePeriod, $limit);

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ProductExport($products), 'top_selling_products.xlsx');
    }
}