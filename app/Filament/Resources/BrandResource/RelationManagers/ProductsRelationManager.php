<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use App\Enums\ProductTypeEnum;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('products')
                    ->tabs([
                        Forms\Components\Tabs\tab::make('Information')
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
                            ])->columns(2),
                        Forms\Components\Tabs\tab::make('Pricing & Inventory')
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
                        Forms\Components\Tabs\tab::make('Additional Information')
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
                                    ->default(now()),
                                Forms\Components\Select::make('category_id')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->required(),
                                Forms\Components\FileUpload::make('image')
                                    ->directory('form-attachments')
                                    ->preserveFilenames()
                                    ->image()
                                    ->imageEditor()
                                    ->default(fn ($record) => $record?->image)
                                    ->columnSpanFull() , 
                            ])->columns(2)
                    ])->columnSpanFull()
                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
                Tables\Columns\TextColumn::make('published_at'),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}